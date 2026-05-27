import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import { Skeleton, SkeletonCard, SkeletonTable, SkeletonChart, SkeletonList } from '../Skeleton';

describe('Skeleton', () => {
  it('should render with default variant', () => {
    const { container } = render(<Skeleton />);
    const el = container.firstChild as HTMLElement;
    expect(el.className).toContain('animate-pulse');
    expect(el.className).toContain('rounded-md');
  });

  it('should render circular variant', () => {
    const { container } = render(<Skeleton variant="circular" />);
    const el = container.firstChild as HTMLElement;
    expect(el.className).toContain('rounded-full');
  });

  it('should render text variant', () => {
    const { container } = render(<Skeleton variant="text" />);
    const el = container.firstChild as HTMLElement;
    expect(el.className).toContain('rounded');
  });

  it('should apply custom width and height', () => {
    const { container } = render(<Skeleton width={100} height={50} />);
    const el = container.firstChild as HTMLElement;
    expect(el.style.width).toBe('100px');
    expect(el.style.height).toBe('50px');
  });

  it('should apply custom className', () => {
    const { container } = render(<Skeleton className="custom-class" />);
    const el = container.firstChild as HTMLElement;
    expect(el.className).toContain('custom-class');
  });
});

describe('SkeletonCard', () => {
  it('should render without crashing', () => {
    const { container } = render(<SkeletonCard />);
    expect(container.firstChild).toBeTruthy();
  });
});

describe('SkeletonTable', () => {
  it('should render with default rows and cols', () => {
    const { container } = render(<SkeletonTable />);
    const rows = container.querySelectorAll('.border-b');
    expect(rows.length).toBe(6);
  });

  it('should render with custom rows and cols', () => {
    const { container } = render(<SkeletonTable rows={3} cols={2} />);
    const rows = container.querySelectorAll('.border-b');
    expect(rows.length).toBe(4);
  });
});

describe('SkeletonChart', () => {
  it('should render without crashing', () => {
    const { container } = render(<SkeletonChart />);
    expect(container.firstChild).toBeTruthy();
  });
});

describe('SkeletonList', () => {
  it('should render with default items', () => {
    const { container } = render(<SkeletonList />);
    const items = container.querySelectorAll('.bg-white');
    expect(items.length).toBe(3);
  });

  it('should render with custom items', () => {
    const { container } = render(<SkeletonList items={5} />);
    const items = container.querySelectorAll('.bg-white');
    expect(items.length).toBe(5);
  });
});
