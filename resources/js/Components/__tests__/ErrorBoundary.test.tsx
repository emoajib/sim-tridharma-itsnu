import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen } from '@testing-library/react';
import ErrorBoundary from '../ErrorBoundary';

// Store original console.error
const originalConsoleError = console.error;

describe('ErrorBoundary', () => {
  beforeEach(() => {
    // Suppress console.error from React error logging in tests
    console.error = vi.fn();
  });

  afterEach(() => {
    console.error = originalConsoleError;
  });

  it('should render children when no error', () => {
    render(
      <ErrorBoundary>
        <div>Test Content</div>
      </ErrorBoundary>
    );

    expect(screen.getByText('Test Content')).toBeInTheDocument();
  });

  it('should display error UI when child throws', () => {
    const ThrowingComponent = () => {
      throw new Error('Test error');
    };

    render(
      <ErrorBoundary>
        <ThrowingComponent />
      </ErrorBoundary>
    );

    // Default error UI should be displayed
    expect(screen.getByText('Terjadi Kesalahan')).toBeInTheDocument();
    expect(screen.getByText('Silakan muat ulang halaman.')).toBeInTheDocument();
    expect(screen.getByText('Muat Ulang')).toBeInTheDocument();
  });

  it('should render custom fallback when provided', () => {
    const ThrowingComponent = () => {
      throw new Error('Test error');
    };

    render(
      <ErrorBoundary fallback={<div>Custom Error UI</div>}>
        <ThrowingComponent />
      </ErrorBoundary>
    );

    expect(screen.getByText('Custom Error UI')).toBeInTheDocument();
    expect(screen.queryByText('Terjadi Kesalahan')).not.toBeInTheDocument();
  });
});
