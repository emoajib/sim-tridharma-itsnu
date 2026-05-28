import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import DangerButton from '../DangerButton';

describe('DangerButton', () => {
    it('renders children', () => {
        render(<DangerButton>Delete</DangerButton>);
        expect(screen.getByText('Delete')).toBeInTheDocument();
    });

    it('renders in disabled state', () => {
        render(<DangerButton disabled>Delete</DangerButton>);
        const button = screen.getByText('Delete');
        expect(button).toBeDisabled();
    });

    it('applies custom className', () => {
        render(<DangerButton className="custom-btn">Delete</DangerButton>);
        const button = screen.getByText('Delete');
        expect(button.className).toContain('custom-btn');
    });

    it('calls onClick handler when clicked', () => {
        const handleClick = vi.fn();
        render(<DangerButton onClick={handleClick}>Delete</DangerButton>);
        fireEvent.click(screen.getByText('Delete'));
        expect(handleClick).toHaveBeenCalledTimes(1);
    });

    it('does not call onClick when disabled', () => {
        const handleClick = vi.fn();
        render(
            <DangerButton disabled onClick={handleClick}>
                Delete
            </DangerButton>,
        );
        fireEvent.click(screen.getByText('Delete'));
        expect(handleClick).not.toHaveBeenCalled();
    });

    it('applies disabled styling class', () => {
        render(<DangerButton disabled>Delete</DangerButton>);
        const button = screen.getByText('Delete');
        expect(button.className).toContain('opacity-25');
    });

    it('has danger-specific styling', () => {
        render(<DangerButton>Delete</DangerButton>);
        const button = screen.getByText('Delete');
        expect(button.className).toContain('bg-red-600');
        expect(button.className).toContain('text-white');
    });

    it('renders as a button element', () => {
        render(<DangerButton>Danger</DangerButton>);
        expect(screen.getByText('Danger').tagName).toBe('BUTTON');
    });

    it('forwards additional HTML button props', () => {
        render(
            <DangerButton data-testid="danger-btn" type="submit">
                Delete
            </DangerButton>,
        );
        const button = screen.getByTestId('danger-btn');
        expect(button.getAttribute('type')).toBe('submit');
    });
});
