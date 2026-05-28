import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import PrimaryButton from '../PrimaryButton';

describe('PrimaryButton', () => {
    it('renders children', () => {
        render(<PrimaryButton>Save</PrimaryButton>);
        expect(screen.getByText('Save')).toBeInTheDocument();
    });

    it('renders in disabled state', () => {
        render(<PrimaryButton disabled>Save</PrimaryButton>);
        const button = screen.getByText('Save');
        expect(button).toBeDisabled();
    });

    it('applies custom className', () => {
        render(<PrimaryButton className="custom-btn">Save</PrimaryButton>);
        const button = screen.getByText('Save');
        expect(button.className).toContain('custom-btn');
    });

    it('calls onClick handler when clicked', () => {
        const handleClick = vi.fn();
        render(<PrimaryButton onClick={handleClick}>Save</PrimaryButton>);
        fireEvent.click(screen.getByText('Save'));
        expect(handleClick).toHaveBeenCalledTimes(1);
    });

    it('does not call onClick when disabled', () => {
        const handleClick = vi.fn();
        render(
            <PrimaryButton disabled onClick={handleClick}>
                Save
            </PrimaryButton>,
        );
        fireEvent.click(screen.getByText('Save'));
        expect(handleClick).not.toHaveBeenCalled();
    });

    it('applies disabled styling class', () => {
        render(<PrimaryButton disabled>Save</PrimaryButton>);
        const button = screen.getByText('Save');
        expect(button.className).toContain('opacity-25');
    });

    it('renders as a button element', () => {
        render(<PrimaryButton>Submit</PrimaryButton>);
        expect(screen.getByText('Submit').tagName).toBe('BUTTON');
    });

    it('forwards additional HTML button props', () => {
        render(
            <PrimaryButton data-testid="primary-btn" type="submit">
                Submit
            </PrimaryButton>,
        );
        const button = screen.getByTestId('primary-btn');
        expect(button.getAttribute('type')).toBe('submit');
    });
});
