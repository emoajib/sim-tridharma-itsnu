import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import ApplicationLogo from '../ApplicationLogo';

describe('ApplicationLogo', () => {
    it('renders without crashing', () => {
        const { container } = render(<ApplicationLogo />);
        expect(container.firstChild).toBeInTheDocument();
    });

    it('renders the logo text A', () => {
        render(<ApplicationLogo />);
        expect(screen.getByText('A')).toBeInTheDocument();
    });

    it('renders ITSNU text', () => {
        render(<ApplicationLogo />);
        expect(screen.getByText('ITSNU')).toBeInTheDocument();
    });

    it('renders Akreditasi text', () => {
        render(<ApplicationLogo />);
        expect(screen.getByText('Akreditasi')).toBeInTheDocument();
    });

    it('renders logo image when logoUrl is provided', () => {
        render(<ApplicationLogo logoUrl="/images/logo.png" />);
        const img = screen.getByAltText('Logo');
        expect(img).toBeInTheDocument();
        expect(img.getAttribute('src')).toBe('/images/logo.png');
    });

    it('does not render the A text when logoUrl is provided', () => {
        render(<ApplicationLogo logoUrl="/images/logo.png" />);
        expect(screen.queryByText('A')).not.toBeInTheDocument();
    });

    it('applies dark text classes when isDark=true', () => {
        render(<ApplicationLogo isDark={true} />);
        const itsnu = screen.getByText('ITSNU');
        expect(itsnu.className).toContain('text-white');
        const akreditasi = screen.getByText('Akreditasi');
        expect(akreditasi.className).toContain('text-indigo-300');
    });

    it('applies light text classes by default', () => {
        render(<ApplicationLogo />);
        const itsnu = screen.getByText('ITSNU');
        expect(itsnu.className).toContain('text-gray-800');
        const akreditasi = screen.getByText('Akreditasi');
        expect(akreditasi.className).toContain('text-indigo-600');
    });
});
