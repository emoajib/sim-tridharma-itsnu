import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import SeverityBadge from '../SeverityBadge';

describe('SeverityBadge', () => {
    it('renders severity text for ringan', () => {
        render(<SeverityBadge severity="ringan" />);
        expect(screen.getByText('Ringan')).toBeInTheDocument();
    });

    it('renders severity text for sedang', () => {
        render(<SeverityBadge severity="sedang" />);
        expect(screen.getByText('Sedang')).toBeInTheDocument();
    });

    it('renders severity text for berat', () => {
        render(<SeverityBadge severity="berat" />);
        expect(screen.getByText('Berat')).toBeInTheDocument();
    });

    it('renders severity text for kritis', () => {
        render(<SeverityBadge severity="kritis" />);
        expect(screen.getByText('Kritis')).toBeInTheDocument();
    });

    it('applies green color for ringan severity', () => {
        render(<SeverityBadge severity="ringan" />);
        const badge = screen.getByText('Ringan');
        expect(badge.className).toContain('bg-green-100');
        expect(badge.className).toContain('text-green-800');
    });

    it('applies yellow color for sedang severity', () => {
        render(<SeverityBadge severity="sedang" />);
        const badge = screen.getByText('Sedang');
        expect(badge.className).toContain('bg-yellow-100');
        expect(badge.className).toContain('text-yellow-800');
    });

    it('applies orange color for berat severity', () => {
        render(<SeverityBadge severity="berat" />);
        const badge = screen.getByText('Berat');
        expect(badge.className).toContain('bg-orange-100');
        expect(badge.className).toContain('text-orange-800');
    });

    it('applies red color for kritis severity', () => {
        render(<SeverityBadge severity="kritis" />);
        const badge = screen.getByText('Kritis');
        expect(badge.className).toContain('bg-red-100');
        expect(badge.className).toContain('text-red-800');
    });

    it('renders with sm size by default', () => {
        render(<SeverityBadge severity="ringan" />);
        const badge = screen.getByText('Ringan');
        expect(badge.className).toContain('text-xs');
    });

    it('renders with md size when specified', () => {
        render(<SeverityBadge severity="ringan" size="md" />);
        const badge = screen.getByText('Ringan');
        expect(badge.className).toContain('text-sm');
        expect(badge.className).toContain('px-3');
    });
});
