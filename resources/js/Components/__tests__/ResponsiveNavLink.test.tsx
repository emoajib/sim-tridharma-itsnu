import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import ResponsiveNavLink from '../ResponsiveNavLink';

vi.mock('@inertiajs/react', () => ({
    Link: ({ children, ...props }: any) => <a {...props}>{children}</a>,
}));

describe('ResponsiveNavLink', () => {
    it('renders children', () => {
        render(<ResponsiveNavLink href="/">Home</ResponsiveNavLink>);
        expect(screen.getByText('Home')).toBeInTheDocument();
    });

    it('renders with href attribute', () => {
        render(
            <ResponsiveNavLink href="/profile">Profile</ResponsiveNavLink>,
        );
        const link = screen.getByText('Profile');
        expect(link.getAttribute('href')).toBe('/profile');
    });

    it('applies active styling when active=true', () => {
        render(
            <ResponsiveNavLink href="/" active={true}>
                Dashboard
            </ResponsiveNavLink>,
        );
        const link = screen.getByText('Dashboard');
        expect(link.className).toContain('border-indigo-400');
        expect(link.className).toContain('bg-indigo-50');
        expect(link.className).toContain('text-indigo-700');
    });

    it('applies inactive styling when active=false', () => {
        render(
            <ResponsiveNavLink href="/" active={false}>
                Dashboard
            </ResponsiveNavLink>,
        );
        const link = screen.getByText('Dashboard');
        expect(link.className).toContain('border-transparent');
        expect(link.className).toContain('text-gray-600');
    });

    it('defaults to inactive when active prop is not provided', () => {
        render(<ResponsiveNavLink href="/">Default</ResponsiveNavLink>);
        const link = screen.getByText('Default');
        expect(link.className).toContain('border-transparent');
        expect(link.className).toContain('text-gray-600');
    });

    it('applies custom className', () => {
        render(
            <ResponsiveNavLink href="/" className="extra-class">
                Link
            </ResponsiveNavLink>,
        );
        const link = screen.getByText('Link');
        expect(link.className).toContain('extra-class');
    });

    it('renders as an anchor element (mocked Link)', () => {
        render(<ResponsiveNavLink href="/test">Test</ResponsiveNavLink>);
        expect(screen.getByText('Test').tagName).toBe('A');
    });
});
