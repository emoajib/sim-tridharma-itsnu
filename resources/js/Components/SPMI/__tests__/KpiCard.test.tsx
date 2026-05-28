import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import KpiCard from '../KpiCard';

vi.mock('@inertiajs/react', () => ({
    Link: ({ children, href, ...props }: any) => (
        <a href={href} {...props}>
            {children}
        </a>
    ),
}));

describe('KpiCard', () => {
    it('renders title', () => {
        render(<KpiCard title="Total Dosen" value={42} />);
        expect(screen.getByText('Total Dosen')).toBeInTheDocument();
    });

    it('renders value', () => {
        render(<KpiCard title="Total Dosen" value={42} />);
        expect(screen.getByText('42')).toBeInTheDocument();
    });

    it('renders string value', () => {
        render(<KpiCard title="Akreditasi" value="A" />);
        expect(screen.getByText('A')).toBeInTheDocument();
    });

    it('renders trend indicator with up direction', () => {
        render(
            <KpiCard
                title="Dosen S3"
                value={15}
                trend={{ value: 10, direction: 'up' }}
            />,
        );
        expect(screen.getByText('10%')).toBeInTheDocument();
    });

    it('renders trend indicator with down direction', () => {
        render(
            <KpiCard
                title="Dosen S3"
                value={15}
                trend={{ value: 5, direction: 'down' }}
            />,
        );
        expect(screen.getByText('5%')).toBeInTheDocument();
    });

    it('renders trend indicator with flat direction', () => {
        render(
            <KpiCard
                title="Dosen S3"
                value={15}
                trend={{ value: 0, direction: 'flat' }}
            />,
        );
        expect(screen.getByText('0%')).toBeInTheDocument();
    });

    it('wraps in a link when link prop is provided', () => {
        render(
            <KpiCard title="Detail" value={99} link="/detail" />,
        );
        const link = screen.getByText('Detail').closest('a');
        expect(link).toBeInTheDocument();
        expect(link?.getAttribute('href')).toBe('/detail');
    });

    it('does not wrap in a link when link prop is not provided', () => {
        render(<KpiCard title="Standalone" value={50} />);
        const titleEl = screen.getByText('Standalone');
        expect(titleEl.closest('a')).toBeNull();
    });

    it('renders with default blue color variant', () => {
        const { container } = render(<KpiCard title="Default" value={1} />);
        const card = container.firstChild as HTMLElement;
        expect(card.className).toContain('border-l-blue-500');
    });

    it('renders with specified color variant', () => {
        const { container } = render(
            <KpiCard title="Green Card" value={1} color="green" />,
        );
        const card = container.firstChild as HTMLElement;
        expect(card.className).toContain('border-l-green-500');
    });
});
