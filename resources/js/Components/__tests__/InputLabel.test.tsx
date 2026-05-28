import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import InputLabel from '../InputLabel';

describe('InputLabel', () => {
    it('renders label text via value prop', () => {
        render(<InputLabel value="Email Address" />);
        expect(screen.getByText('Email Address')).toBeInTheDocument();
        expect(screen.getByText('Email Address').tagName).toBe('LABEL');
    });

    it('renders children instead of value', () => {
        render(<InputLabel><span>Child Label</span></InputLabel>);
        expect(screen.getByText('Child Label')).toBeInTheDocument();
    });

    it('renders value over children when both provided', () => {
        render(
            <InputLabel value="Value Text">
                <span>Child Text</span>
            </InputLabel>,
        );
        expect(screen.getByText('Value Text')).toBeInTheDocument();
        expect(screen.queryByText('Child Text')).not.toBeInTheDocument();
    });

    it('applies for attribute', () => {
        render(<InputLabel htmlFor="email-input" value="Email" />);
        const label = screen.getByText('Email');
        expect(label.getAttribute('for')).toBe('email-input');
    });

    it('applies custom className', () => {
        render(<InputLabel value="Name" className="custom-label" />);
        const label = screen.getByText('Name');
        expect(label.className).toContain('custom-label');
    });

    it('applies default styling classes', () => {
        render(<InputLabel value="Default Styled" />);
        const label = screen.getByText('Default Styled');
        expect(label.className).toContain('block');
        expect(label.className).toContain('text-sm');
        expect(label.className).toContain('font-medium');
        expect(label.className).toContain('text-gray-700');
    });

    it('renders nothing when no value and no children', () => {
        const { container } = render(<InputLabel />);
        expect(container.querySelector('label')).toBeInTheDocument();
        expect(container.querySelector('label')?.textContent).toBe('');
    });
});
