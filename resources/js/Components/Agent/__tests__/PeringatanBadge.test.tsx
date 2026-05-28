import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import PeringatanBadge from '../PeringatanBadge';

vi.mock('@inertiajs/react', () => ({
    Link: ({ children, href, ...props }: any) => (
        <a href={href} {...props}>
            {children}
        </a>
    ),
}));

// Mock global route function
global.route = vi.fn((name: string) => `/mock/${name}`);

describe('PeringatanBadge', () => {
    it('renders no warning state when all counts are zero', () => {
        render(<PeringatanBadge />);
        expect(
            screen.getByText('Tidak ada peringatan'),
        ).toBeInTheDocument();
    });

    it('does not show "Tidak ada peringatan" when showLabel is false', () => {
        render(<PeringatanBadge showLabel={false} />);
        expect(
            screen.queryByText('Tidak ada peringatan'),
        ).not.toBeInTheDocument();
    });

    it('renders critical count', () => {
        render(<PeringatanBadge critical={3} />);
        const link = screen.getByText('3 peringatan');
        expect(link).toBeInTheDocument();
        expect(screen.getByText('3')).toBeInTheDocument();
    });

    it('renders warning count', () => {
        render(<PeringatanBadge warning={5} />);
        expect(screen.getByText('5')).toBeInTheDocument();
    });

    it('renders info count', () => {
        render(<PeringatanBadge info={2} />);
        expect(screen.getByText('2')).toBeInTheDocument();
    });

    it('renders all counts together', () => {
        render(
            <PeringatanBadge critical={1} warning={2} info={3} />,
        );
        expect(screen.getByText('1')).toBeInTheDocument();
        expect(screen.getByText('2')).toBeInTheDocument();
        expect(screen.getByText('3')).toBeInTheDocument();
        expect(screen.getByText('6 peringatan')).toBeInTheDocument();
    });

    it('renders unread badge when unread > 0', () => {
        render(<PeringatanBadge critical={1} unread={3} />);
        expect(screen.getByText('3')).toBeInTheDocument();
    });

    it('shows 9+ for unread count > 9', () => {
        render(<PeringatanBadge critical={1} unread={15} />);
        expect(screen.getByText('9+')).toBeInTheDocument();
    });

    it('renders link with correct href when warnings exist', () => {
        render(<PeringatanBadge critical={1} />);
        const link = screen.getByText('1 peringatan').closest('a');
        expect(link?.getAttribute('href')).toBe('/mock/peringatan');
    });

    it('does not render total label when showLabel is false', () => {
        render(<PeringatanBadge critical={1} showLabel={false} />);
        expect(
            screen.queryByText('1 peringatan'),
        ).not.toBeInTheDocument();
    });
});
