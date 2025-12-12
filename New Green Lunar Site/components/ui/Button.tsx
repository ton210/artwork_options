import { cn } from '@/lib/utils/cn';
import { ButtonHTMLAttributes, forwardRef } from 'react';

export interface ButtonProps extends ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'secondary' | 'outline';
  size?: 'sm' | 'md' | 'lg';
  children: React.ReactNode;
}

const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant = 'primary', size = 'md', children, ...props }, ref) => {
    return (
      <button
        ref={ref}
        className={cn(
          'inline-flex items-center justify-center rounded-lg font-heading font-semibold transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed',
          {
            // Variants
            'bg-brand-green text-brand-dark hover:bg-brand-green/90 hover:shadow-lg hover:-translate-y-0.5':
              variant === 'primary',
            'bg-brand-blue text-white hover:bg-brand-blue/90 hover:shadow-lg hover:-translate-y-0.5':
              variant === 'secondary',
            'border-2 border-brand-dark text-brand-dark hover:bg-brand-dark hover:text-white':
              variant === 'outline',
            // Sizes
            'px-4 py-2 text-sm': size === 'sm',
            'px-6 py-3 text-base': size === 'md',
            'px-8 py-4 text-lg': size === 'lg',
          },
          className
        )}
        {...props}
      >
        {children}
      </button>
    );
  }
);

Button.displayName = 'Button';

export default Button;
