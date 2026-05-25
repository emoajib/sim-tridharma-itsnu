# BRUTAL ANALYSIS: Role-Based Data Filtering and ERKAT Submission

## 1. Inti masalah sebenarnya
**Role-based data filtering is inconsistently implemented and contains critical authorization gaps in ERKAT submission.** While dashboard filtering appears mostly correct, the ERKAT (RKAT) module lacks proper authorization checks during proposal submission, allowing users to potentially submit proposals for any program study regardless of their role. Additionally, the Dosen role is incorrectly filtered in RKAT listing due to a column mismatch.

## 2. Analisis mendalam
### Dashboard Filtering (Mostly Correct)
- Controller sets scopeParams based on activeRole():
  - Dosen: dosen_id → correctly filters DashboardService queries by dosen_id
  - Kaprodi/Staf Prodi: prodi_id → correctly filters by prodi_id
  - Dekan: fakultas_id → correctly filters by fakultas_id via prodi relationship
- DashboardService.applyScope() properly handles these parameters for all relevant models (Dosen, Prodi, DokumenBukti, etc.) with appropriate relationship traversals.

### ERKAT Submission (CRITICAL FLAW)
- RkatController::store() accepts validated prodi_id from request WITHOUT verifying user authorization
- StoreUsulanRequest.authorize() returns true for all requests → no authorization check
- Missing validation: User can submit ERKAT for any prodi_id by simply changing the request parameter
- Example: A Dosen from Teknik Informatik could submit ERKAT for Fakultas Kedokteran prodi by guessing or tampering with prodi_id

### ERKAT Listing (PARTIALLY BROKEN)
- RkatController::index() uses HasRoleScope::applyScope() with scopeField='prodi_id'
- HasRoleScope trait:
  - Dosen role: attempts to filter by `dosen_id` column on UsulanRkat table → **COLUMN DOES NOT EXIST** (UsulanRkat has user_id, not dosen_id)
  - This will cause query errors or return empty results for Dosen users
  - Dekan role: correctly filters to prodi under their fakultas
  - Kaprodi/Staf Prodi: correctly filters to their own prodi

### Permission Middleware Gaps
- PermissionMiddleware exists but appears unused for ERKAT routes
- No middleware protection on RKAT submission endpoints to enforce role-based unit restrictions

## 3. Blind spot yang jarang disadari
**The dual-model confusion in HasRoleScope trait:** The trait attempts to apply the same scoping logic to fundamentally different models (User-facing vs transactional models). For transactional models like UsulanRkat:
- Dosen scope should filter via the user's dosen_id → prodi_id relationship (not direct dosen_id column)
- Current implementation assumes all models have dosen_id column → false for UsulanRkat, potentially true for others
- This creates inconsistent behavior across modules where the same trait is reused without model-specific adaptation

## 4. Strategi terbaik
1. **Implement proper authorization in ERKAT submission:** Add authorization check in StoreUsulanRequest or controller store method
2. **Fix HasRoleScope for transactional models:** Create model-specific scoping or add configuration parameter
3. **Standardize role checking:** Use Laravel Gates/Policies for complex authorization logic instead of scattering checks
4. **Add middleware protection:** Ensure all ERKAT endpoints are protected by permission middleware
5. **Validate scopeParams early:** Reject invalid scopeParams combinations in DashboardController

## 5. Langkah eksekusi paling efektif
### Immediate Fixes (Do these first):
1. **Add authorization to StoreUsulanRequest:**
   ```php
   public function authorize(): bool
   {
       $user = $this->user();
       $role = $user->activeRole();
       $prodiId = $this->input('prodi_id');

       return match($role) {
           'Dosen' => $user->dosen_id && $user->dosen->prodi_id == $prodi_id,
           'Kaprodi', 'Staf Prodi' => $user->prodi_id == $prodi_id,
           'Dekan' => $user->prodi && 
                   $user->prodi->fakultas_id && 
                   Prodi::where('fakultas_id', $user->prodi->fakultas_id)
                         ->where('id', $prodi_id)
                         ->exists(),
           default => false,
       };
   }
   ```

2. **Fix HasRoleScope for UsulanRkat-like models:**
   ```php
   // In HasRoleScope trait, add model detection:
   public function applyScope(Builder $query, User $user, string $scopeField = 'prodi_id', array $options = []): Builder
   {
       // Add special handling for models without direct dosen_id
       if ($user->activeRole() === 'Dosen' && ! Schema::hasColumn($query->getModel()->getTable(), 'dosen_id')) {
           // For transactional models, filter via user's dosen->prodi relationship
           return $query->whereHas('user.dosen.prodi', fn($q) => $q->where('id', $user->dosen->prodi_id));
       }
       
       // ... rest of existing logic
   }
   ```

3. **Add middleware to RKAT routes:** Ensure `auth` and `permission` middleware are applied to all RKAT endpoints

### Strategic Improvements:
1. **Replace custom scoping with Laravel Policies:** Create RkatPolicy with create/update/viewAny methods
2. **Centralize scope validation:** Create ScopeValidator service to validate scopeParams consistency
3. **Add audit logging:** Log all ERKAT submission attempts (authorized/unauthorized) for security monitoring

## 6. Kesalahan fatal yang harus dihindari
1. **Never trust client-sent prodi_id for authorization** → Always verify against authenticated user's role and relationships
2. **Avoid column name assumptions in reusable traits** → Traits must introspect model structure or accept configuration
3. **Don't reuse scoping logic across disparate model types** → Transactional models (UsulanRkat) need different scoping than master data models (Prodi, Dosen)
4. **Never omit middleware on mutation endpoints** → All POST/PUT/PDELETE endpoints must have authorization middleware
5. **Don't rely solely on frontend UI restrictions** → Backend must enforce authorization regardless of UI state

## 7. Optimasi level expert
1. **Implement role-based query scopes as model traits:**
   ```php
   // In UsulanRkat model
   public function scopeForUser($query)
   {
       return $query->when(auth()->user()->activeRole() === 'Dosen', fn($q) => 
                       $q->whereHas('user.dosen.prodi', fn($q2) => $q2->where('id', auth()->user()->dosen->prodi_id))
                   )->when(in_array(auth()->user()->activeRole(), ['Kaprodi', 'Staf Prodi']), fn($q) => 
                       $q->where('prodi_id', auth()->user()->prodi_id)
                   )->when(auth()->user()->activeRole() === 'Dekan', fn($q) => 
                       $q->whereHas('prodi', fn($q2) => $q2->where('fakultas_id', auth()->user()->prodi->fakultas_id))
                   );
   }
   ```

2. **Use Laravel's built-in authorization gates:**
   ```php
   // In AuthServiceProvider
   Gate::define('submit-rkat', function ($user, $prodiId = null) {
       return match($user->activeRole()) {
           'Dosen' => $user->dosen_id && $user->dosen->prodi_id == $prodiId,
           'Kaprodi', 'Staf Prodi' => $user->prodi_id == $prodiId,
           'Dekan' => $user->prodi && 
                   $user->prodi->fakultas_id && 
                   Prodi::where('fakultas_id', $user->prodi->fakultas_id)
                         ->where('id', $prodiId)
                         ->exists(),
           default => false,
       };
   });
   ```

3. **Add database-level security:** Implement row-level security (RLS) via database views or PostgreSQL policies for critical data

## 8. Kesimpulan akhir
**The system has a dangerous illusion of security:** While 129 tests pass and dashboard filtering appears functional, the ERKAT module contains critical authorization bypass vulnerabilities. Users can submit proposals for any program study by manipulating the prodi_id parameter, and the Dosen role experiences broken functionality in RKAT listing due to a column mismatch in the reusable scoping trait.

**Immediate action required:** 
1. Fix the authorization gap in ERKAT submission within 24 hours
2. Correct the HasRoleScope trait to handle transactional models properly
3. Implement middleware protection on all ERKAT endpoints
4. Add comprehensive security tests covering role-based authorization boundaries

The current implementation violates the principle of least privilege and exposes the institution to potential data integrity risks and unauthorized budget allocations. Trust but verify – the tests passing only validate happy paths, not security boundaries.