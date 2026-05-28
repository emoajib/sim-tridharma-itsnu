import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import TextInput from '../TextInput';

describe('TextInput', () => {
    it('renders an input element', () => {
        render(<TextInput />);
        const input = screen.getByRole('textbox');
        expect(input).toBeInTheDocument();
    });

    it('applies custom className', () => {
        render(<TextInput className="custom-class" />);
        const input = screen.getByRole('textbox');
        expect(input.className).toContain('custom-class');
    });

    it('handles onChange event', () => {
        const handleChange = vi.fn();
        render(<TextInput onChange={handleChange} />);
        const input = screen.getByRole('textbox');
        fireEvent.change(input, { target: { value: 'new value' } });
        expect(handleChange).toHaveBeenCalledTimes(1);
    });

    it('forwards ref and provides focus method', () => {
        const ref = { current: null as any };
        render(<TextInput ref={ref} />);
        expect(ref.current).not.toBeNull();
        expect(typeof ref.current?.focus).toBe('function');
    });

    it('renders with type password', () => {
        render(<TextInput type="password" data-testid="pw-input" />);
        const input = screen.getByTestId('pw-input');
        expect(input.getAttribute('type')).toBe('password');
    });

    it('renders with default type text when no type specified', () => {
        render(<TextInput data-testid="default-input" />);
        const input = screen.getByTestId('default-input');
        expect(input.getAttribute('type')).toBe('text');
    });

    it('forwards additional input props', () => {
        render(
            <TextInput
                placeholder="Enter name"
                disabled={true}
                data-testid="prop-input"
            />,
        );
        const input = screen.getByTestId('prop-input');
        expect(input.getAttribute('placeholder')).toBe('Enter name');
        expect(input).toBeDisabled();
    });

    it('applies default styling classes', () => {
        render(<TextInput data-testid="style-input" />);
        const input = screen.getByTestId('style-input');
        expect(input.className).toContain('rounded-md');
        expect(input.className).toContain('border-gray-300');
    });
});
