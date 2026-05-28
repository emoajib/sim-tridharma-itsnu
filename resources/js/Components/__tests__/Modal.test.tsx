import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import Modal from '../Modal';

vi.mock('@headlessui/react', () => ({
    Dialog: ({ children, onClose }: any) => (
        <div data-testid="modal-dialog" onClick={onClose}>
            {children}
        </div>
    ),
    DialogPanel: ({ children }: any) => <div>{children}</div>,
    Transition: ({ children, show }: any) =>
        show ? <div>{children}</div> : null,
    TransitionChild: ({ children }: any) => <div>{children}</div>,
}));

describe('Modal', () => {
    it('renders children when show=true', () => {
        render(
            <Modal show={true} onClose={() => {}}>
                <div>Modal Content</div>
            </Modal>,
        );
        expect(screen.getByText('Modal Content')).toBeInTheDocument();
    });

    it('is hidden when show=false', () => {
        render(
            <Modal show={false} onClose={() => {}}>
                <div>Modal Content</div>
            </Modal>,
        );
        expect(screen.queryByText('Modal Content')).not.toBeInTheDocument();
    });

    it('calls onClose when backdrop is clicked and closeable is true', () => {
        const onClose = vi.fn();
        render(
            <Modal show={true} onClose={onClose}>
                <div>Content</div>
            </Modal>,
        );
        fireEvent.click(screen.getByTestId('modal-dialog'));
        expect(onClose).toHaveBeenCalledTimes(1);
    });

    it('does not call onClose when closeable is false', () => {
        const onClose = vi.fn();
        render(
            <Modal show={true} closeable={false} onClose={onClose}>
                <div>Content</div>
            </Modal>,
        );
        fireEvent.click(screen.getByTestId('modal-dialog'));
        expect(onClose).not.toHaveBeenCalled();
    });

    it('renders children elements', () => {
        render(
            <Modal show={true} onClose={() => {}}>
                <span>First Child</span>
                <span>Second Child</span>
            </Modal>,
        );
        expect(screen.getByText('First Child')).toBeInTheDocument();
        expect(screen.getByText('Second Child')).toBeInTheDocument();
    });

    it('renders without title prop (no title rendering in component)', () => {
        render(
            <Modal show={true} onClose={() => {}}>
                <div>No Title Component</div>
            </Modal>,
        );
        expect(screen.getByText('No Title Component')).toBeInTheDocument();
    });
});
