import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import StatusBadge from '../StatusBadge';

describe('StatusBadge', () => {
    it('renders status text', () => {
        render(<StatusBadge status="draft" workflowType="audit" />);
        expect(screen.getByText('Draft')).toBeInTheDocument();
    });

    it('renders different status texts', () => {
        const { rerender } = render(
            <StatusBadge status="submitted" workflowType="audit" />,
        );
        expect(screen.getByText('Submitted')).toBeInTheDocument();

        rerender(<StatusBadge status="in_progress" workflowType="audit" />);
        expect(screen.getByText('In Progress')).toBeInTheDocument();

        rerender(<StatusBadge status="verified" workflowType="audit" />);
        expect(screen.getByText('Verified')).toBeInTheDocument();

        rerender(<StatusBadge status="rejected" workflowType="audit" />);
        expect(screen.getByText('Rejected')).toBeInTheDocument();

        rerender(<StatusBadge status="closed" workflowType="audit" />);
        expect(screen.getByText('Closed')).toBeInTheDocument();
    });

    it('applies correct color for draft status', () => {
        render(<StatusBadge status="draft" workflowType="audit" />);
        const badge = screen.getByText('Draft');
        expect(badge.className).toContain('bg-gray-100');
        expect(badge.className).toContain('text-gray-800');
    });

    it('applies correct color for verified status', () => {
        render(<StatusBadge status="verified" workflowType="audit" />);
        const badge = screen.getByText('Verified');
        expect(badge.className).toContain('bg-green-100');
        expect(badge.className).toContain('text-green-800');
    });

    it('applies correct color for rejected status', () => {
        render(<StatusBadge status="rejected" workflowType="audit" />);
        const badge = screen.getByText('Rejected');
        expect(badge.className).toContain('bg-red-100');
        expect(badge.className).toContain('text-red-800');
    });

    it('uses correct color map for capa workflow type', () => {
        render(<StatusBadge status="in_progress" workflowType="capa" />);
        const badge = screen.getByText('In Progress');
        expect(badge.className).toContain('bg-yellow-100');
    });

    it('uses correct color map for dokumen workflow type', () => {
        render(<StatusBadge status="approved" workflowType="dokumen" />);
        const badge = screen.getByText('Approved');
        expect(badge.className).toContain('bg-green-100');
    });

    it('renders with sm size by default', () => {
        render(<StatusBadge status="draft" workflowType="audit" />);
        const badge = screen.getByText('Draft');
        expect(badge.className).toContain('text-xs');
    });

    it('renders with md size when specified', () => {
        render(<StatusBadge status="draft" workflowType="audit" size="md" />);
        const badge = screen.getByText('Draft');
        expect(badge.className).toContain('text-sm');
        expect(badge.className).toContain('px-3');
    });

    it('capitalizes underscored status labels', () => {
        render(
            <StatusBadge
                status="awaiting_verification"
                workflowType="audit"
            />,
        );
        expect(screen.getByText('Awaiting Verification')).toBeInTheDocument();
    });
});
