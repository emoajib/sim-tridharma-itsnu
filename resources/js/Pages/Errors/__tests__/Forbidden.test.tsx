import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import Forbidden from '../Forbidden';

vi.mock('@inertiajs/react', () => ({
    Head: ({ title }: any) => <title>{title}</title>,
}));

describe('Forbidden', () => {
    it('renders 403 status code', () => {
        render(<Forbidden />);
        expect(screen.getByText('403')).toBeInTheDocument();
    });

    it('renders access denied message', () => {
        render(<Forbidden />);
        expect(screen.getByText('Akses Ditolak')).toBeInTheDocument();
    });

    it('renders description text', () => {
        render(<Forbidden />);
        expect(
            screen.getByText(
                'Anda tidak memiliki izin untuk mengakses halaman ini.',
            ),
        ).toBeInTheDocument();
    });

    it('renders back to home link', () => {
        render(<Forbidden />);
        const link = screen.getByText('Kembali ke Beranda');
        expect(link).toBeInTheDocument();
        expect(link.getAttribute('href')).toBe('/');
    });

    it('sets document title on mount', () => {
        render(<Forbidden />);
        expect(document.title).toBe('403 - Akses Ditolak');
    });
});
