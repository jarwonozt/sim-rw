import { Link } from '@inertiajs/react';

export default function Pagination({ links }) {
    if (!links || links.length <= 3) {
        return null;
    }

    return (
        <nav className="flex flex-wrap items-center gap-1">
            {links.map((link, index) => (
                <Link
                    key={index}
                    href={link.url ?? '#'}
                    preserveScroll
                    className={
                        'rounded-md px-3 py-1.5 text-sm ' +
                        (link.active
                            ? 'bg-emerald-600 text-white'
                            : link.url
                              ? 'text-gray-600 hover:bg-gray-100'
                              : 'cursor-not-allowed text-gray-300')
                    }
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ))}
        </nav>
    );
}
