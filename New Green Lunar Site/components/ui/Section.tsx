import { cn } from '@/lib/utils/cn';
import { HTMLAttributes } from 'react';

export interface SectionProps extends HTMLAttributes<HTMLElement> {
  children: React.ReactNode;
  background?: 'white' | 'light' | 'dark';
}

export default function Section({
  className,
  children,
  background = 'white',
  ...props
}: SectionProps) {
  return (
    <section
      className={cn(
        'py-16 lg:py-24',
        {
          'bg-white': background === 'white',
          'bg-brand-light': background === 'light',
          'bg-brand-dark text-white': background === 'dark',
        },
        className
      )}
      {...props}
    >
      {children}
    </section>
  );
}
