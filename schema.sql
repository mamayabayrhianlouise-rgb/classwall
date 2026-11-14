CREATE TABLE IF NOT EXISTS public.messages (
    id SERIAL PRIMARY KEY,
    author VARCHAR(100) NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

SELECT * FROM public.messages;

SELECT * FROM public.messages
ORDER BY created_at DESC;