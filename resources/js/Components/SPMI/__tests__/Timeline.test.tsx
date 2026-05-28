import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import Timeline from '../Timeline';

describe('Timeline', () => {
    const baseItem = {
        date: '2024-01-15T10:00:00Z',
        action: 'Mengajukan dokumen akreditasi',
        user: 'Dr. Santoso',
        type: 'created' as const,
    };

    it('renders timeline items', () => {
        render(<Timeline items={[baseItem]} />);
        expect(
            screen.getByText('Mengajukan dokumen akreditasi'),
        ).toBeInTheDocument();
    });

    it('renders user name', () => {
        render(<Timeline items={[baseItem]} />);
        expect(screen.getByText(/Dr. Santoso/)).toBeInTheDocument();
    });

    it('renders description when provided', () => {
        const item = {
            ...baseItem,
            description: 'Dokumen telah dilengkapi',
        };
        render(<Timeline items={[item]} />);
        expect(screen.getByText('Dokumen telah dilengkapi')).toBeInTheDocument();
    });

    it('renders empty state when no items', () => {
        render(<Timeline items={[]} />);
        expect(
            screen.getByText('Belum ada aktivitas.'),
        ).toBeInTheDocument();
    });

    it('renders multiple items', () => {
        const items = [
            baseItem,
            {
                date: '2024-01-16T14:00:00Z',
                action: 'Memverifikasi dokumen',
                user: 'Dr. Wijaya',
                type: 'verified' as const,
            },
            {
                date: '2024-01-17T09:00:00Z',
                action: 'Menyetujui dokumen',
                user: 'Prof. Hidayat',
                type: 'transition' as const,
            },
        ];
        render(<Timeline items={items} />);
        expect(
            screen.getByText('Mengajukan dokumen akreditasi'),
        ).toBeInTheDocument();
        expect(
            screen.getByText('Memverifikasi dokumen'),
        ).toBeInTheDocument();
        expect(
            screen.getByText('Menyetujui dokumen'),
        ).toBeInTheDocument();
    });

    it('renders formatted date', () => {
        render(<Timeline items={[baseItem]} />);
        // The formatDate function uses id-ID locale, so "15 Jan 2024"
        expect(screen.getByText(/15/)).toBeInTheDocument();
    });
});
