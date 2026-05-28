import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import ServerError from '../ServerError';

vi.mock('@inertiajs/react', () => ({
    Head: ({ title }: any) => <title>{title}</title>,
}));

describe('ServerError', () => {
    it('renders 500 status code', () => {
        render(<ServerError />);
        expect(screen.getByText('500')).toBeInTheDocument();
    });

    it('renders server error message', () => {
        render(<ServerError />);
        expect(
            screen.getByText('Terjadi Kesalahan'),
        ).toBeInTheDocument();
    });

    it('renders description text', () => {
        render(<ServerError />);
        expect(
            screen.getByText(
                'Maaf, terjadi kesalahan pada server. Silakan coba lagi beberapa saat.',
            ),
        ).toBeInTheDocument();
    });

    it('renders back to home link', () => {
        render(<ServerError />);
        const link = screen.getByText('Kembali ke Beranda');
        expect(link).toBeInTheDocument();
        expect(link.getAttribute('href')).toBe('/');
    });

    it('sets document title on mount', () => {
        render(<ServerError />);
        expect(document.title).toBe('500 - Kesalahan Server');
    });
});
