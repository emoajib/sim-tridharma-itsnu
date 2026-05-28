import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import EarlyWarning from '../EarlyWarning';

describe('EarlyWarning', () => {
    it('renders warning message for kritis type', () => {
        const warnings = [
            { type: 'kritis' as const, message: 'Nilai akreditasi menurun drastis' },
        ];
        render(<EarlyWarning warnings={warnings} />);
        expect(
            screen.getByText('Nilai akreditasi menurun drastis'),
        ).toBeInTheDocument();
    });

    it('renders multiple warning messages', () => {
        const warnings = [
            { type: 'kritis' as const, message: 'Warning 1' },
            { type: 'overdue' as const, message: 'Warning 2' },
        ];
        render(<EarlyWarning warnings={warnings} />);
        expect(screen.getByText('Warning 1')).toBeInTheDocument();
        expect(screen.getByText('Warning 2')).toBeInTheDocument();
    });

    it('renders prodi tag when provided', () => {
        const warnings = [
            {
                type: 'kritis' as const,
                message: 'Masalah akreditasi',
                prodi: 'Teknik Informatika',
            },
        ];
        render(<EarlyWarning warnings={warnings} />);
        expect(screen.getByText('Teknik Informatika')).toBeInTheDocument();
    });

    it('renders days count when provided', () => {
        const warnings = [
            {
                type: 'overdue' as const,
                message: 'Dokumen overdue',
                days: 5,
            },
        ];
        render(<EarlyWarning warnings={warnings} />);
        expect(screen.getByText('5 hari')).toBeInTheDocument();
    });

    it('renders no-warning state when warnings array is empty', () => {
        render(<EarlyWarning warnings={[]} />);
        expect(
            screen.getByText(
                'Tidak ada peringatan. Semua berjalan normal.',
            ),
        ).toBeInTheDocument();
    });

    it('renders warning type labels', () => {
        const warnings = [
            { type: 'kritis' as const, message: 'Critical!' },
            { type: 'overdue' as const, message: 'Overdue!' },
            { type: 'mendekat' as const, message: 'Approaching!' },
            { type: 'info' as const, message: 'Info!' },
        ];
        render(<EarlyWarning warnings={warnings} />);
        expect(screen.getByText('Kritis')).toBeInTheDocument();
        expect(screen.getByText('Overdue')).toBeInTheDocument();
        expect(screen.getByText('Mendekati Deadline')).toBeInTheDocument();
        expect(screen.getByText('Informasi')).toBeInTheDocument();
    });
});
