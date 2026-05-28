import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Checkbox from '../Checkbox';

describe('Checkbox', () => {
    it('renders a checkbox input', () => {
        render(<Checkbox data-testid="checkbox" />);
        const checkbox = screen.getByTestId('checkbox');
        expect(checkbox).toBeInTheDocument();
        expect(checkbox.getAttribute('type')).toBe('checkbox');
    });

    it('handles onChange event', () => {
        const handleChange = vi.fn();
        render(<Checkbox data-testid="checkbox" onChange={handleChange} />);
        fireEvent.click(screen.getByTestId('checkbox'));
        expect(handleChange).toHaveBeenCalledTimes(1);
    });

    it('reflects checked state', () => {
        render(<Checkbox data-testid="checkbox" checked={true} readOnly />);
        const checkbox = screen.getByTestId('checkbox') as HTMLInputElement;
        expect(checkbox.checked).toBe(true);
    });

    it('reflects unchecked state', () => {
        render(<Checkbox data-testid="checkbox" checked={false} readOnly />);
        const checkbox = screen.getByTestId('checkbox') as HTMLInputElement;
        expect(checkbox.checked).toBe(false);
    });

    it('renders in disabled state', () => {
        render(<Checkbox data-testid="checkbox" disabled />);
        expect(screen.getByTestId('checkbox')).toBeDisabled();
    });

    it('applies custom className', () => {
        render(<Checkbox data-testid="checkbox" className="custom-class" />);
        const checkbox = screen.getByTestId('checkbox');
        expect(checkbox.className).toContain('custom-class');
    });

    it('applies default styling classes', () => {
        render(<Checkbox data-testid="checkbox" />);
        const checkbox = screen.getByTestId('checkbox');
        expect(checkbox.className).toContain('rounded');
        expect(checkbox.className).toContain('border-gray-300');
        expect(checkbox.className).toContain('text-indigo-600');
    });
});
