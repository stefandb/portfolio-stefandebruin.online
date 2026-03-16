export interface PostSerie {
    id: number;
    title: string;
    slug: string;
    description: string;
}

export interface Post {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    content: string;
    reading_time: number | null;
    status: 'draft' | 'published';
    published_at: string | null;
    post_serie_id: number | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}
