export interface FileItem {
    uuid: string;
    name: string;
    original_name: string;
    url: string;
    mime_type: string;
    size: number;
    is_image: boolean;
    created_at: string;
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
