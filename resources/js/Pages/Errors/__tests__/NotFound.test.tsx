import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import NotFound from '../NotFound';

vi.mock('@inertiajs/react', () => ({
    Head: ({ title }: any) => <title>{title}</title>,
}));

describe('NotFound', () => {
    it('renders 404 status code', () => {
        render(<NotFound />);
        expect(screen.getByText('404')).toBeInTheDocument();
    });

    it('renders page not found message', () => {
        render(<NotFound />);
        expect(
            screen.getByText('Halaman Tidak Ditemukan'),
        ).toBeInTheDocument();
    });

    it('renders description text', () => {
        render(<NotFound />);
        expect(
            screen.getByText(
                'Halaman yang Anda cari tidak tersedia atau telah dipindahkan.',
            ),
        ).toBeInTheDocument();
    });

    it('renders back to home link', () => {
        render(<NotFound />);
        const link = screen.getByText('Kembali ke Beranda');
        expect(link).toBeInTheDocument();
        expect(link.getAttribute('href')).toBe('/');
    });

    it('sets document title on mount', () => {
        render(<NotFound />);
        expect(document.title).toBe('404 - Halaman Tidak Ditemukan');
    });
});
