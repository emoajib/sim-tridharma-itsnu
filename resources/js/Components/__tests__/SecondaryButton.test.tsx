import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import SecondaryButton from '../SecondaryButton';

describe('SecondaryButton', () => {
    it('renders children', () => {
        render(<SecondaryButton>Cancel</SecondaryButton>);
        expect(screen.getByText('Cancel')).toBeInTheDocument();
    });

    it('renders in disabled state', () => {
        render(<SecondaryButton disabled>Cancel</SecondaryButton>);
        const button = screen.getByText('Cancel');
        expect(button).toBeDisabled();
    });

    it('applies custom className', () => {
        render(<SecondaryButton className="custom-btn">Cancel</SecondaryButton>);
        const button = screen.getByText('Cancel');
        expect(button.className).toContain('custom-btn');
    });

    it('calls onClick handler when clicked', () => {
        const handleClick = vi.fn();
        render(
            <SecondaryButton onClick={handleClick}>Cancel</SecondaryButton>,
        );
        fireEvent.click(screen.getByText('Cancel'));
        expect(handleClick).toHaveBeenCalledTimes(1);
    });

    it('does not call onClick when disabled', () => {
        const handleClick = vi.fn();
        render(
            <SecondaryButton disabled onClick={handleClick}>
                Cancel
            </SecondaryButton>,
        );
        fireEvent.click(screen.getByText('Cancel'));
        expect(handleClick).not.toHaveBeenCalled();
    });

    it('renders with default type button', () => {
        render(<SecondaryButton>Cancel</SecondaryButton>);
        const button = screen.getByText('Cancel');
        expect(button.getAttribute('type')).toBe('button');
    });

    it('applies disabled styling class', () => {
        render(<SecondaryButton disabled>Cancel</SecondaryButton>);
        const button = screen.getByText('Cancel');
        expect(button.className).toContain('opacity-25');
    });

    it('renders as a button element', () => {
        render(<SecondaryButton>Secondary</SecondaryButton>);
        expect(screen.getByText('Secondary').tagName).toBe('BUTTON');
    });
});
