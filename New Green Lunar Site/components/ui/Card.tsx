import { cn } from '@/lib/utils/cn';
import { HTMLAttributes } from 'react';

export interface CardProps extends HTMLAttributes<HTMLDivElement> {
  hover?: boolean;
  children: React.ReactNode;
}

export default function Card({ className, hover = true, children, ...props }: CardProps) {
  return (
    <div
      className={cn(
        'bg-white rounded-xl shadow-lg p-6 transition-all duration-300',
        hover && 'hover:shadow-2xl hover:-translate-y-1',
        className
      )}
      {...props}
    >
      {children}
    </div>
  );
}
