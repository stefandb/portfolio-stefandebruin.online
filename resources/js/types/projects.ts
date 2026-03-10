export interface Project {
    id: number;
    title: string;
    slug: string;
    excerpt: string;
    content: string;
    github_url: string | null;
    demo_url: string | null;
    company: string | null;
    role: string | null;
    year: number;
    status: 'draft' | 'published';
    published_at: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}
