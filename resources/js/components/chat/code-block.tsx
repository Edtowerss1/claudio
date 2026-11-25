import { Check, Copy } from 'lucide-react';
import { useRef } from 'react';
import type { ComponentProps } from 'react';
import { useClipboard } from '@/hooks/use-clipboard';

export function codeBlock({ children }: ComponentProps<'pre'>) {
    const pre = useRef<HTMLPreElement>(null);
    const [isCopied, copy] = useClipboard();

    return (
        <div className="group relative">
            {/* highlight.js already adds the necessary classes for syntax highlighting */}
            <pre
                ref={pre}
                className="overflow-hidden rounded-lg bg-transparent p-0"
            >
                {children}
            </pre>
            <button
                type="button"
                aria-label="Copy code"
                onClick={() => copy(pre.current?.innerText ?? '')}
                className="absolute top-2 right-2 rounded-md bg-white/10 p-1.5 text-white opacity-0 group-hover:opacity-100"
            >
                {isCopied ? (
                    <Check className="size-4" />
                ) : (
                    <Copy className="size-4" />
                )}
            </button>
        </div>
    );
}
