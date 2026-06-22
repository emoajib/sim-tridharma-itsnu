import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen } from '@testing-library/react';
import RoleGate from '../RoleGate';

vi.mock('@inertiajs/react', () => ({
  usePage: vi.fn(),
}));

import { usePage } from '@inertiajs/react';
const mockedUsePage = vi.mocked(usePage);

describe('RoleGate', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('should render children when user has matching role', () => {
    mockedUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            role_list: ['admin'],
            permissions: [],
          },
        },
      },
    } as any);

    render(
      <RoleGate roles={['admin']}>
        <div>Admin Content</div>
      </RoleGate>
    );

    expect(screen.getByText('Admin Content')).toBeInTheDocument();
  });

  it('should render fallback when user does not have matching role', () => {
    mockedUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            role_list: ['user'],
            permissions: [],
          },
        },
      },
    } as any);

    render(
      <RoleGate roles={['admin']} fallback={<div>Access Denied</div>}>
        <div>Admin Content</div>
      </RoleGate>
    );

    expect(screen.getByText('Access Denied')).toBeInTheDocument();
    expect(screen.queryByText('Admin Content')).not.toBeInTheDocument();
  });

  it('should render children when user has matching permission', () => {
    mockedUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            role_list: [],
            permissions: ['data-import.view'],
          },
        },
      },
    } as any);

    render(
      <RoleGate permissions={['data-import.view']}>
        <div>Import Content</div>
      </RoleGate>
    );

    expect(screen.getByText('Import Content')).toBeInTheDocument();
  });

  it('should render fallback when user does not have matching permission', () => {
    mockedUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            role_list: [],
            permissions: ['other.view'],
          },
        },
      },
    } as any);

    render(
      <RoleGate permissions={['data-import.view']} fallback={<div>No Access</div>}>
        <div>Import Content</div>
      </RoleGate>
    );

    expect(screen.getByText('No Access')).toBeInTheDocument();
  });

  it('should render fallback when no user', () => {
    mockedUsePage.mockReturnValue({
      props: {
        auth: null,
      },
    } as any);

    render(
      <RoleGate roles={['admin']} fallback={<div>Login Required</div>}>
        <div>Admin Content</div>
      </RoleGate>
    );

    expect(screen.getByText('Login Required')).toBeInTheDocument();
  });

  it('should render children when no roles or permissions specified', () => {
    mockedUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            role_list: ['user'],
            permissions: [],
          },
        },
      },
    } as any);

    render(
      <RoleGate>
        <div>Always Visible</div>
      </RoleGate>
    );

    expect(screen.getByText('Always Visible')).toBeInTheDocument();
  });

  it('should check multiple roles (any match)', () => {
    mockedUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            role_list: ['editor'],
            permissions: [],
          },
        },
      },
    } as any);

    render(
      <RoleGate roles={['admin', 'editor']}>
        <div>Editor Content</div>
      </RoleGate>
    );

    expect(screen.getByText('Editor Content')).toBeInTheDocument();
  });

  it('should check multiple permissions (any match)', () => {
    mockedUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            role_list: [],
            permissions: ['rkat.view'],
          },
        },
      },
    } as any);

    render(
      <RoleGate permissions={['rkat.view', 'rkat.create']}>
        <div>RKAT Content</div>
      </RoleGate>
    );

    expect(screen.getByText('RKAT Content')).toBeInTheDocument();
  });
});
