import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import TooManyRequests from '../TooManyRequests';

vi.mock('@inertiajs/react', () => ({
    Head: ({ title }: any) => <title>{title}</title>,
}));

describe('TooManyRequests', () => {
    it('renders 429 status code', () => {
        render(<TooManyRequests />);
        expect(screen.getByText('429')).toBeInTheDocument();
    });

    it('renders too many requests message', () => {
        render(<TooManyRequests />);
        expect(
            screen.getByText('Terlalu Banyak Permintaan'),
        ).toBeInTheDocument();
    });

    it('renders description text', () => {
        render(<TooManyRequests />);
        expect(
            screen.getByText(
                'Anda telah melakukan terlalu banyak permintaan. Silakan coba lagi nanti.',
            ),
        ).toBeInTheDocument();
    });

    it('renders back to home link', () => {
        render(<TooManyRequests />);
        const link = screen.getByText('Kembali ke Beranda');
        expect(link).toBeInTheDocument();
        expect(link.getAttribute('href')).toBe('/');
    });

    it('sets document title on mount', () => {
        render(<TooManyRequests />);
        expect(document.title).toBe('429 - Terlalu Banyak Permintaan');
    });
});
