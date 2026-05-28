import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Dropdown from '../Dropdown';

vi.mock('@headlessui/react', () => ({
    Transition: ({ children, show }: any) =>
        show ? <div>{children}</div> : null,
}));

vi.mock('@inertiajs/react', () => ({
    Link: ({ children, ...props }: any) => <a {...props}>{children}</a>,
}));

describe('Dropdown', () => {
    it('renders trigger element', () => {
        render(
            <Dropdown>
                <Dropdown.Trigger>
                    <button>Open Menu</button>
                </Dropdown.Trigger>
            </Dropdown>,
        );
        expect(screen.getByText('Open Menu')).toBeInTheDocument();
    });

    it('opens content when trigger is clicked', () => {
        render(
            <Dropdown>
                <Dropdown.Trigger>
                    <button>Toggle</button>
                </Dropdown.Trigger>
                <Dropdown.Content>
                    <div>Menu Items</div>
                </Dropdown.Content>
            </Dropdown>,
        );

        expect(screen.queryByText('Menu Items')).not.toBeInTheDocument();

        fireEvent.click(screen.getByText('Toggle'));
        expect(screen.getByText('Menu Items')).toBeInTheDocument();
    });

    it('closes content when trigger is clicked again', () => {
        render(
            <Dropdown>
                <Dropdown.Trigger>
                    <button>Toggle</button>
                </Dropdown.Trigger>
                <Dropdown.Content>
                    <div>Menu Items</div>
                </Dropdown.Content>
            </Dropdown>,
        );

        fireEvent.click(screen.getByText('Toggle'));
        expect(screen.getByText('Menu Items')).toBeInTheDocument();

        fireEvent.click(screen.getByText('Toggle'));
        expect(screen.queryByText('Menu Items')).not.toBeInTheDocument();
    });

    it('closes content when content backdrop area is clicked', () => {
        render(
            <Dropdown>
                <Dropdown.Trigger>
                    <button>Toggle</button>
                </Dropdown.Trigger>
                <Dropdown.Content>
                    <div>Menu Items</div>
                </Dropdown.Content>
            </Dropdown>,
        );

        fireEvent.click(screen.getByText('Toggle'));
        expect(screen.getByText('Menu Items')).toBeInTheDocument();

        // Click the backdrop overlay (the fixed div behind content)
        const backdrop = document.querySelector('.fixed.inset-0.z-40');
        expect(backdrop).toBeInTheDocument();
        if (backdrop) {
            fireEvent.click(backdrop);
        }
        expect(screen.queryByText('Menu Items')).not.toBeInTheDocument();
    });

    it('aligns content to the right by default', () => {
        render(
            <Dropdown>
                <Dropdown.Trigger>
                    <button>Toggle</button>
                </Dropdown.Trigger>
                <Dropdown.Content>
                    <div>Menu Items</div>
                </Dropdown.Content>
            </Dropdown>,
        );

        fireEvent.click(screen.getByText('Toggle'));
        const menuText = screen.getByText('Menu Items');
        const outerDiv = menuText.parentElement?.parentElement;
        expect(outerDiv?.className).toContain('end-0');
    });

    it('renders Dropdown.Link component', () => {
        render(
            <Dropdown>
                <Dropdown.Trigger>
                    <button>Toggle</button>
                </Dropdown.Trigger>
                <Dropdown.Content>
                    <Dropdown.Link href="/test">Profile</Dropdown.Link>
                </Dropdown.Content>
            </Dropdown>,
        );

        fireEvent.click(screen.getByText('Toggle'));
        const link = screen.getByText('Profile');
        expect(link).toBeInTheDocument();
        expect(link.tagName).toBe('A');
        expect(link.getAttribute('href')).toBe('/test');
    });
});
