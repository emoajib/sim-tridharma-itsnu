import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import InputError from '../InputError';

describe('InputError', () => {
    it('renders error message when provided', () => {
        render(<InputError message="This field is required" />);
        expect(screen.getByText('This field is required')).toBeInTheDocument();
    });

    it('renders nothing when message is null', () => {
        const { container } = render(<InputError message={null} />);
        expect(container.innerHTML).toBe('');
    });

    it('renders nothing when message is undefined', () => {
        const { container } = render(<InputError />);
        expect(container.innerHTML).toBe('');
    });

    it('renders nothing when message is empty string', () => {
        const { container } = render(<InputError message="" />);
        // Empty string is truthy in JS, so it would render an empty <p>
        // Actually, `message ? <p>...</p> : null` — empty string is falsy
        expect(container.innerHTML).toBe('');
    });

    it('applies custom className', () => {
        render(<InputError message="Error!" className="custom-error" />);
        const errorEl = screen.getByText('Error!');
        expect(errorEl.className).toContain('custom-error');
    });

    it('applies default styling classes', () => {
        render(<InputError message="Something went wrong" />);
        const errorEl = screen.getByText('Something went wrong');
        expect(errorEl.className).toContain('text-sm');
        expect(errorEl.className).toContain('text-red-600');
    });

    it('renders as a paragraph element', () => {
        render(<InputError message="Error message" />);
        const errorEl = screen.getByText('Error message');
        expect(errorEl.tagName).toBe('P');
    });

    it('forwards additional HTML props', () => {
        render(
            <InputError
                message="Test error"
                data-testid="error-message"
                id="custom-id"
            />,
        );
        const errorEl = screen.getByTestId('error-message');
        expect(errorEl.getAttribute('id')).toBe('custom-id');
    });
});
