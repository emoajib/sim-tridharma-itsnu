import { describe, it, expect, vi } from 'vitest';
import { render, screen } from '@testing-library/react';
import NavLink from '../NavLink';

vi.mock('@inertiajs/react', () => ({
    Link: ({ children, ...props }: any) => <a {...props}>{children}</a>,
}));

describe('NavLink', () => {
    it('renders children', () => {
        render(<NavLink href="/">Home</NavLink>);
        expect(screen.getByText('Home')).toBeInTheDocument();
    });

    it('renders with href attribute', () => {
        render(<NavLink href="/dashboard">Dashboard</NavLink>);
        const link = screen.getByText('Dashboard');
        expect(link.getAttribute('href')).toBe('/dashboard');
    });

    it('applies active styling when active=true', () => {
        render(
            <NavLink href="/" active={true}>
                Home
            </NavLink>,
        );
        const link = screen.getByText('Home');
        expect(link.className).toContain('border-indigo-400');
        expect(link.className).toContain('text-gray-900');
    });

    it('applies inactive styling when active=false', () => {
        render(
            <NavLink href="/" active={false}>
                Home
            </NavLink>,
        );
        const link = screen.getByText('Home');
        expect(link.className).toContain('border-transparent');
        expect(link.className).toContain('text-gray-500');
    });

    it('applies custom className alongside default classes', () => {
        render(
            <NavLink href="/" className="extra-class">
                Home
            </NavLink>,
        );
        const link = screen.getByText('Home');
        expect(link.className).toContain('extra-class');
        expect(link.className).toContain('inline-flex');
    });

    it('renders as an anchor element (mocked Link)', () => {
        render(<NavLink href="/test">Test</NavLink>);
        expect(screen.getByText('Test').tagName).toBe('A');
    });
});
