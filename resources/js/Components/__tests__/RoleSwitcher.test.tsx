import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import RoleSwitcher from '../RoleSwitcher';

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
    router: { 
      post: vi.fn(),
      // Add other router methods if needed by other tests
      get: vi.fn(),
      put: vi.fn(),
      patch: vi.fn(),
      delete: vi.fn(),
    },
}));

import { usePage, router } from '@inertiajs/react';
const mockedUsePage = vi.mocked(usePage);
const mockedRouter = vi.mocked(router);

// Mock global route function
global.route = vi.fn((name: string) => `/mock/${name}`) as any;

describe('RoleSwitcher', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders nothing when user has single role', () => {
        mockedUsePage.mockReturnValue({
            props: {
                auth: {
                    user: {
                        role_list: ['Dosen'],
                        active_role: 'Dosen',
                    },
                },
            },
        } as any);

        const { container } = render(<RoleSwitcher />);
        expect(container.innerHTML).toBe('');
    });

    it('renders nothing when user has no role_list', () => {
        mockedUsePage.mockReturnValue({
            props: {
                auth: {
                    user: {},
                },
            },
        } as any);

        const { container } = render(<RoleSwitcher />);
        expect(container.innerHTML).toBe('');
    });

    it('renders nothing when auth is null', () => {
        mockedUsePage.mockReturnValue({
            props: { auth: null },
        } as any);

        const { container } = render(<RoleSwitcher />);
        expect(container.innerHTML).toBe('');
    });

    it('renders active role button when multiple roles exist', () => {
        mockedUsePage.mockReturnValue({
            props: {
                auth: {
                    user: {
                        role_list: ['Dosen', 'Kaprodi'],
                        active_role: 'Dosen',
                    },
                },
            },
        } as any);

        render(<RoleSwitcher />);
        expect(screen.getByText('Dosen')).toBeInTheDocument();
    });

    it('opens dropdown when role button is clicked', () => {
        mockedUsePage.mockReturnValue({
            props: {
                auth: {
                    user: {
                        role_list: ['Dosen', 'Kaprodi'],
                        active_role: 'Dosen',
                    },
                },
            },
        } as any);

        render(<RoleSwitcher />);
        fireEvent.click(screen.getByText('Dosen'));
        expect(screen.getByText('Kaprodi')).toBeInTheDocument();
        expect(screen.getByText('Switch Role')).toBeInTheDocument();
    });

    it('calls router.post when switching role', () => {
        mockedUsePage.mockReturnValue({
            props: {
                auth: {
                    user: {
                        role_list: ['Dosen', 'Kaprodi'],
                        active_role: 'Dosen',
                    },
                },
            },
        } as any);

        render(<RoleSwitcher />);
        fireEvent.click(screen.getByText('Dosen'));
        fireEvent.click(screen.getByText('Kaprodi'));

        expect(mockedRouter.post).toHaveBeenCalledWith(
            '/mock/role.switch',
            { role: 'Kaprodi' },
            { preserveState: true, preserveScroll: true },
        );
    });

    it('shows all roles in dropdown when opened', () => {
        mockedUsePage.mockReturnValue({
            props: {
                auth: {
                    user: {
                        role_list: ['Admin', 'Dosen', 'Kaprodi'],
                        active_role: 'Admin',
                    },
                },
            },
        } as any);

        render(<RoleSwitcher />);
        fireEvent.click(screen.getByText('Admin'));

        expect(screen.getByText('Dosen')).toBeInTheDocument();
        expect(screen.getByText('Kaprodi')).toBeInTheDocument();
    });
});
