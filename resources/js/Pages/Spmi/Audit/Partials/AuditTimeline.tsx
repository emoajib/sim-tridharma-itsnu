import Timeline from '@/Components/SPMI/Timeline';

interface HistoryUser {
    id: number;
    name: string;
}

interface AuditHistoryItem {
    id: number;
    audit_mutu_id: number;
    user_id: number | null;
    field: string;
    old_value: string | null;
    new_value: string | null;
    action: string;
    created_at: string;
    user?: HistoryUser | null;
}

interface AuditTimelineProps {
    histories: AuditHistoryItem[];
}

function mapActionToType(action: string): 'created' | 'updated' | 'transition' | 'verified' | 'rejected' | 'assigned' {
    if (action === 'audit_created') return 'created';
    if (action === 'field_updated') return 'updated';
    if (action?.startsWith('status_transition')) return 'transition';
    if (action === 'pic_assigned') return 'assigned';
    if (action === 'audit_verified') return 'verified';
    if (action === 'audit_rejected') return 'rejected';
    return 'updated';
}

function getActionLabel(action: string, field: string, oldValue: string | null, newValue: string | null): string {
    if (action === 'audit_created') return 'Audit dibuat';
    if (action === 'pic_assigned') return 'PIC ditugaskan';
    if (action === 'capa_auto_created') return 'CAPA dibuat otomatis';
    if (action === 'capa_submitted_for_verification') return 'CAPA diajukan verifikasi';
    if (action === 'capa_verified') return 'CAPA diverifikasi';
    if (action === 'capa_rejected') return 'CAPA ditolak';
    if (action === 'capa_updated') return 'CAPA diperbarui';
    if (action?.startsWith('status_transition')) {
        const oldLabel = oldValue?.replace(/_/g, ' ') || '-';
        const newLabel = newValue?.replace(/_/g, ' ') || '-';
        return `Status berubah: ${oldLabel} → ${newLabel}`;
    }
    if (action === 'field_updated') {
        const fieldLabel = field.replace(/_/g, ' ');
        return `Field "${fieldLabel}" diperbarui`;
    }
    return action || 'Perubahan';
}

export default function AuditTimeline({ histories }: AuditTimelineProps) {
    const timelineItems = histories
        .sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
        .map((h) => ({
            date: h.created_at,
            action: getActionLabel(h.action, h.field, h.old_value, h.new_value),
            user: h.user?.name || 'System',
            description: h.action === 'field_updated' ? `${h.old_value || '-'} → ${h.new_value || '-'}` : undefined,
            type: mapActionToType(h.action),
        }));

    return <Timeline items={timelineItems} />;
}
