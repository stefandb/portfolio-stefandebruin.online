export interface FileItem {
    uuid: string;
    name: string;
    original_name: string;
    url: string;
    webp_url: string | null;
    thumbnail_url: string | null;
    og_url: string | null;
    responsive_urls: Record<number, string> | null;
    mime_type: string;
    size: number;
    is_image: boolean;
    created_at: string;
    alt?: string;
}

export interface FileCursorPage {
    data: FileItem[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        path: string;
        per_page: number;
        next_cursor: string | null;
        prev_cursor: string | null;
    };
}
