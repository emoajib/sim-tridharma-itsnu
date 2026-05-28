import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import SimilarityBadge from '../SimilarityBadge';

describe('SimilarityBadge', () => {
    it('renders similarity percentage', () => {
        render(<SimilarityBadge score={0.85} />);
        expect(screen.getByText('85%')).toBeInTheDocument();
    });

    it('renders 0% for score of 0', () => {
        render(<SimilarityBadge score={0} />);
        expect(screen.getByText('0%')).toBeInTheDocument();
    });

    it('renders 100% for score of 1', () => {
        render(<SimilarityBadge score={1} />);
        expect(screen.getByText('100%')).toBeInTheDocument();
    });

    it('renders green color for high score (>= 0.8)', () => {
        render(<SimilarityBadge score={0.95} />);
        const badge = screen.getByText('95%');
        expect(badge.className).toContain('bg-green-100');
        expect(badge.className).toContain('text-green-800');
    });

    it('renders yellow color for medium score (>= 0.6 and < 0.8)', () => {
        render(<SimilarityBadge score={0.75} />);
        const badge = screen.getByText('75%');
        expect(badge.className).toContain('bg-yellow-100');
        expect(badge.className).toContain('text-yellow-800');
    });

    it('renders red color for low score (< 0.6)', () => {
        render(<SimilarityBadge score={0.45} />);
        const badge = screen.getByText('45%');
        expect(badge.className).toContain('bg-red-100');
        expect(badge.className).toContain('text-red-800');
    });

    it('renders red color for score exactly at 0.6 boundary', () => {
        // 0.6 >= 0.6, so it should be yellow (medium)
        render(<SimilarityBadge score={0.6} />);
        const badge = screen.getByText('60%');
        expect(badge.className).toContain('bg-yellow-100');
    });

    it('renders red color for score just below 0.6', () => {
        render(<SimilarityBadge score={0.59} />);
        const badge = screen.getByText('59%');
        expect(badge.className).toContain('bg-red-100');
    });

    it('renders with sm size by default', () => {
        render(<SimilarityBadge score={0.5} />);
        const badge = screen.getByText('50%');
        expect(badge.className).toContain('text-xs');
    });

    it('renders with md size when specified', () => {
        render(<SimilarityBadge score={0.5} size="md" />);
        const badge = screen.getByText('50%');
        expect(badge.className).toContain('text-sm');
    });
});
