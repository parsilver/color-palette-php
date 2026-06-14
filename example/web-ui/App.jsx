const { useState, useEffect, useRef, createContext, useContext, useCallback } = React;

/* ------------------------------------------------------------------ *
 *  Chroma — an editorial "color lab" front-end for farzai/color-palette
 *  Paper + ink, a single vermilion accent, the user's colors as heroes.
 * ------------------------------------------------------------------ */

// ---- Theme (light/dark) ----------------------------------------------------
const ThemeContext = createContext();

function ThemeProvider({ children }) {
    const [theme, setTheme] = useState(() => {
        const saved = localStorage.getItem('chroma-theme');
        if (saved) return saved;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    });

    useEffect(() => {
        const root = document.documentElement;
        root.classList.remove('light', 'dark');
        root.classList.add(theme);
        localStorage.setItem('chroma-theme', theme);
    }, [theme]);

    return (
        <ThemeContext.Provider value={{ theme, toggle: () => setTheme(t => (t === 'light' ? 'dark' : 'light')) }}>
            {children}
        </ThemeContext.Provider>
    );
}

const useTheme = () => useContext(ThemeContext);

// ---- copy-to-clipboard with feedback --------------------------------------
function useCopy() {
    const [copied, setCopied] = useState(null);
    const timer = useRef(null);
    const copy = useCallback((text) => {
        navigator.clipboard?.writeText(text);
        setCopied(text);
        clearTimeout(timer.current);
        timer.current = setTimeout(() => setCopied(null), 1100);
    }, []);
    return { copy, copied };
}

// ---- readable text color over a hex ---------------------------------------
function readableOn(hex) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return (0.299 * r + 0.587 * g + 0.114 * b) / 255 > 0.56 ? '#16140f' : '#fdfcf8';
}

// ---- small building blocks -------------------------------------------------
function Eyebrow({ children, className = '' }) {
    return <p className={`eyebrow text-muted-foreground ${className}`}>{children}</p>;
}

function Card({ children, className = '' }) {
    return (
        <div className={`rounded-lg border border-border bg-card text-card-foreground shadow-[0_1px_0_hsl(var(--border))] ${className}`}>
            {children}
        </div>
    );
}

function AccentButton({ children, className = '', ...props }) {
    return (
        <button
            {...props}
            className={`inline-flex items-center justify-center gap-2 rounded-md bg-accent px-4 text-sm font-semibold text-accent-foreground transition hover:brightness-105 active:scale-[0.98] disabled:opacity-40 disabled:pointer-events-none ${className}`}
        >
            {children}
        </button>
    );
}

function GhostButton({ children, className = '', ...props }) {
    return (
        <button
            {...props}
            className={`inline-flex items-center justify-center gap-2 rounded-md border border-border bg-transparent px-3 text-sm font-medium transition hover:bg-muted active:scale-[0.98] disabled:opacity-40 disabled:pointer-events-none ${className}`}
        >
            {children}
        </button>
    );
}

function Spinner({ className = '' }) {
    return (
        <svg className={`animate-spin ${className}`} viewBox="0 0 24 24" fill="none">
            <circle className="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="3" />
            <path className="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V1a11 11 0 00-11 11h3z" />
        </svg>
    );
}

// Hex color field: native picker + monospace text input, kept in sync.
function HexField({ label, value, onChange }) {
    return (
        <label className="block space-y-1.5">
            <span className="eyebrow text-muted-foreground">{label}</span>
            <span className="flex gap-2">
                <input
                    type="color"
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    className="h-11 w-12 shrink-0 rounded-md border border-border"
                    aria-label={`${label} swatch`}
                />
                <input
                    type="text"
                    value={value}
                    onChange={(e) => onChange(e.target.value)}
                    spellCheck={false}
                    className="h-11 w-full rounded-md border border-input bg-background px-3 font-mono text-sm uppercase tracking-wide"
                />
            </span>
        </label>
    );
}

// A single color tile with click-to-copy + "copied" feedback + staggered reveal.
function Swatch({ hex, sub, index = 0, onCopy, copied }) {
    const isCopied = copied === hex;
    return (
        <button
            onClick={() => onCopy(hex)}
            style={{ animationDelay: `${Math.min(index * 45, 400)}ms` }}
            className="swatch rise-in group block w-full overflow-hidden rounded-md border border-border text-left"
            title={`Copy ${hex}`}
        >
            <span className="relative block h-20 w-full" style={{ backgroundColor: hex }}>
                <span
                    className={`absolute inset-0 grid place-items-center text-xs font-semibold tracking-wide transition-opacity ${isCopied ? 'opacity-100' : 'opacity-0'}`}
                    style={{ color: readableOn(hex), backgroundColor: hex }}
                >
                    Copied
                </span>
            </span>
            <span className="block bg-card px-2 py-1.5">
                <span className="block text-center font-mono text-xs font-medium uppercase">{hex}</span>
                {sub && <span className="mt-0.5 block text-center font-mono text-[10px] text-muted-foreground">{sub}</span>}
            </span>
        </button>
    );
}

// ---- App shell -------------------------------------------------------------
function App() {
    return (
        <ThemeProvider>
            <Shell />
        </ThemeProvider>
    );
}

const TABS = [
    { id: 'extract', label: 'Extract', n: '01' },
    { id: 'generate', label: 'Generate', n: '02' },
    { id: 'manipulate', label: 'Manipulate', n: '03' },
    { id: 'accessibility', label: 'Contrast', n: '04' },
];

function Shell() {
    const [tab, setTab] = useState('extract');
    const { theme, toggle } = useTheme();

    return (
        <div className="mx-auto min-h-screen max-w-6xl px-5 sm:px-8">
            {/* Masthead */}
            <header className="flex items-end justify-between gap-4 border-b border-foreground/15 pt-8 pb-5">
                <div className="flex items-center gap-3">
                    <span className="grid grid-cols-2 gap-0.5" aria-hidden="true">
                        {['hsl(var(--accent))', '#2b6cb0', '#2f855a', '#1a1a1a'].map((c, i) => (
                            <span key={i} className="h-3.5 w-3.5 rounded-[3px]" style={{ backgroundColor: c }} />
                        ))}
                    </span>
                    <div>
                        <h1 className="font-display text-3xl font-semibold leading-none tracking-tight">Chroma</h1>
                        <p className="eyebrow mt-1 text-muted-foreground">Color Palette Lab</p>
                    </div>
                </div>
                <div className="flex items-center gap-4">
                    <a
                        href="https://github.com/parsilver/color-palette-php"
                        target="_blank"
                        rel="noreferrer"
                        className="hidden font-mono text-xs text-muted-foreground transition hover:text-foreground sm:block"
                    >
                        farzai/color-palette
                    </a>
                    <button
                        onClick={toggle}
                        className="grid h-9 w-9 place-items-center rounded-md border border-border transition hover:bg-muted"
                        aria-label="Toggle theme"
                    >
                        {theme === 'light' ? (
                            <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        ) : (
                            <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                <path strokeLinecap="round" strokeLinejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36 6.36l-.7-.7M6.34 6.34l-.7-.7m12.72 0l-.7.7M6.34 17.66l-.7.7M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        )}
                    </button>
                </div>
            </header>

            {/* Editorial tab bar with an animated ink underline */}
            <nav className="flex gap-7 border-b border-border">
                {TABS.map((t) => {
                    const active = tab === t.id;
                    return (
                        <button
                            key={t.id}
                            onClick={() => setTab(t.id)}
                            className={`group relative -mb-px py-3.5 transition ${active ? 'text-foreground' : 'text-muted-foreground hover:text-foreground'}`}
                        >
                            <span className="eyebrow flex items-center gap-1.5">
                                <span className={active ? 'text-accent' : 'text-muted-foreground/60'}>{t.n}</span>
                                {t.label}
                            </span>
                            {active && (
                                <span
                                    className="absolute inset-x-0 bottom-0 h-[2px] origin-left bg-accent"
                                    style={{ animation: 'inkUnderline 0.3s cubic-bezier(0.2,0.7,0.2,1)' }}
                                />
                            )}
                        </button>
                    );
                })}
            </nav>

            <main className="py-8">
                {tab === 'extract' && <ImageExtractor key="extract" />}
                {tab === 'generate' && <PaletteGenerator key="generate" />}
                {tab === 'manipulate' && <ColorManipulator key="manipulate" />}
                {tab === 'accessibility' && <AccessibilityChecker key="accessibility" />}
            </main>

            <footer className="border-t border-border py-6">
                <p className="font-mono text-xs text-muted-foreground">
                    Built on <span className="text-foreground">farzai/color-palette</span> — extraction, schemes, manipulation & WCAG contrast, all server-side in PHP.
                </p>
            </footer>
        </div>
    );
}

// ---- 01 · Extract ----------------------------------------------------------
function ImageExtractor() {
    const [image, setImage] = useState(null);
    const [preview, setPreview] = useState(null);
    const [loading, setLoading] = useState(false);
    const [colors, setColors] = useState([]);
    const [theme, setTheme] = useState(null);
    const [count, setCount] = useState(6);
    const [dragOver, setDragOver] = useState(false);
    const [error, setError] = useState(null);
    const fileInput = useRef(null);
    const { copy, copied } = useCopy();

    const extract = async (source, n) => {
        setLoading(true); setError(null); setColors([]); setTheme(null);
        try {
            const body = new FormData();
            if (source.type === 'url') body.append('url', source.url);
            else body.append('image', source.file);
            body.append('count', n);
            const res = await fetch('api.php?action=extract', { method: 'POST', body });
            const data = await res.json();
            if (data.success) { setColors(data.colors); setTheme(data.theme); }
            else setError(data.error || 'Extraction failed');
        } catch (e) { setError(e.message); }
        finally { setLoading(false); }
    };

    const processFile = (file) => {
        if (!file.type.startsWith('image/')) return setError('Please choose an image file.');
        if (file.size > 10 * 1024 * 1024) return setError(`Too large (${(file.size / 1048576).toFixed(1)} MB). Max 10 MB.`);
        setError(null);
        const reader = new FileReader();
        reader.onload = (e) => { setPreview(e.target.result); setImage({ type: 'file', file }); };
        reader.readAsDataURL(file);
    };

    const loadDemo = () => {
        const url = `https://picsum.photos/900/600?random=${Date.now()}`;
        setPreview(url);
        const src = { type: 'url', url };
        setImage(src);
        extract(src, count);
    };

    const exportFile = (name, content, type) => {
        const a = document.createElement('a');
        a.href = URL.createObjectURL(new Blob([content], { type }));
        a.download = name; a.click();
    };
    const exportJSON = () => exportFile('palette.json', JSON.stringify({
        colors: colors.map(c => c.hex),
        theme: theme ? Object.fromEntries(Object.entries(theme).map(([k, v]) => [k, v.hex])) : null,
    }, null, 2), 'application/json');
    const exportCSS = () => {
        let css = ':root {\n' + colors.map((c, i) => `  --color-${i + 1}: ${c.hex};`).join('\n');
        if (theme) css += '\n' + Object.entries(theme).map(([k, v]) => `  --${k.replace('_', '-')}: ${v.hex};`).join('\n');
        exportFile('palette.css', css + '\n}\n', 'text/css');
    };

    return (
        <div className="grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,360px)_1fr]">
            {/* Source */}
            <div className="space-y-4">
                <Eyebrow>Source image</Eyebrow>
                <div
                    onDragOver={(e) => { e.preventDefault(); setDragOver(true); }}
                    onDragLeave={() => setDragOver(false)}
                    onDrop={(e) => { e.preventDefault(); setDragOver(false); if (e.dataTransfer.files[0]) processFile(e.dataTransfer.files[0]); }}
                    onClick={() => fileInput.current.click()}
                    className={`upload-zone grid cursor-pointer place-items-center rounded-lg border-2 border-dashed px-6 py-10 text-center ${dragOver ? 'drag-over' : 'border-border hover:border-muted-foreground/50'}`}
                >
                    <svg className="mb-3 h-7 w-7 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
                    </svg>
                    <p className="text-sm font-medium">Drop an image or click to browse</p>
                    <p className="mt-1 font-mono text-[11px] text-muted-foreground">PNG · JPG · GIF · WebP — max 10 MB</p>
                    <input ref={fileInput} type="file" accept="image/*" className="hidden" onChange={(e) => e.target.files[0] && processFile(e.target.files[0])} />
                </div>

                {preview && (
                    <div className="overflow-hidden rounded-lg border border-border">
                        <img src={preview} alt="Preview" className="block w-full" />
                    </div>
                )}

                <div className="space-y-1.5">
                    <div className="flex items-baseline justify-between">
                        <span className="eyebrow text-muted-foreground">Swatches</span>
                        <span className="font-mono text-sm">{count}</span>
                    </div>
                    <input type="range" min="3" max="15" value={count} onChange={(e) => setCount(+e.target.value)} className="h-1.5 w-full cursor-pointer" />
                </div>

                <div className="flex gap-2">
                    <GhostButton onClick={loadDemo} className="h-11">
                        <svg className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path strokeLinecap="round" strokeLinejoin="round" d="M4 4v5h.58M19.4 9A8 8 0 004.6 9m0 0H9m11 11v-5h-.58m0 0A8 8 0 015.6 17m13.8-2H15" /></svg>
                        Demo
                    </GhostButton>
                    <AccentButton onClick={() => image && extract(image, count)} disabled={!image || loading} className="h-11 flex-1">
                        {loading ? <><Spinner className="h-4 w-4" /> Extracting…</> : 'Extract palette'}
                    </AccentButton>
                </div>

                {error && <p className="font-mono text-xs text-accent">{error}</p>}
            </div>

            {/* Results */}
            <div className="space-y-8">
                {colors.length === 0 && !loading && (
                    <div className="grid h-full min-h-[300px] place-items-center rounded-lg border border-dashed border-border">
                        <div className="text-center">
                            <p className="font-display text-2xl text-muted-foreground">Nothing extracted yet</p>
                            <p className="mt-1 font-mono text-xs text-muted-foreground">Pick an image, or hit Demo for a random one.</p>
                        </div>
                    </div>
                )}

                {colors.length > 0 && (
                    <>
                        <section className="space-y-3">
                            <div className="flex items-center justify-between">
                                <Eyebrow>Extracted · {colors.length}</Eyebrow>
                                <div className="flex gap-2">
                                    <GhostButton onClick={exportJSON} className="h-8 text-xs">JSON</GhostButton>
                                    <GhostButton onClick={exportCSS} className="h-8 text-xs">CSS</GhostButton>
                                </div>
                            </div>
                            <div className="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6">
                                {colors.map((c, i) => (
                                    <Swatch key={i} hex={c.hex} index={i} sub={c.isLight ? 'light' : 'dark'} onCopy={copy} copied={copied} />
                                ))}
                            </div>
                        </section>

                        {theme && (
                            <section className="space-y-3">
                                <Eyebrow>Suggested UI roles</Eyebrow>
                                <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    {Object.entries(theme).map(([role, v], i) => (
                                        <Swatch key={role} hex={v.hex} index={i} sub={role.replace('_', ' ')} onCopy={copy} copied={copied} />
                                    ))}
                                </div>
                            </section>
                        )}

                        <Showcase colors={colors} />
                    </>
                )}
            </div>
        </div>
    );
}

// Demonstrate the extracted colors inside real UI elements.
function Showcase({ colors }) {
    if (!colors?.length) return null;
    const c = (i, fallback) => (colors[i] ? colors[i].hex : fallback);
    return (
        <section className="space-y-4">
            <Eyebrow>Colors in context</Eyebrow>
            <Card className="space-y-7 p-6">
                <div className="space-y-2.5">
                    <p className="font-mono text-[11px] text-muted-foreground">Buttons</p>
                    <div className="flex flex-wrap gap-2.5">
                        {colors[0] && <button className="h-10 rounded-md px-4 text-sm font-semibold transition hover:brightness-105" style={{ backgroundColor: c(0), color: readableOn(c(0)) }}>Primary</button>}
                        {colors[1] && <button className="h-10 rounded-md px-4 text-sm font-semibold transition hover:brightness-105" style={{ backgroundColor: c(1), color: readableOn(c(1)) }}>Secondary</button>}
                        {colors[2] && <button className="h-10 rounded-md border-2 bg-transparent px-4 text-sm font-semibold transition hover:bg-muted" style={{ borderColor: c(2), color: c(2) }}>Outline</button>}
                    </div>
                </div>

                <div className="space-y-2.5">
                    <p className="font-mono text-[11px] text-muted-foreground">Cards</p>
                    <div className="grid grid-cols-1 gap-3 md:grid-cols-3">
                        {colors[0] && (
                            <div className="overflow-hidden rounded-md border border-border">
                                <div className="px-4 py-3 text-sm font-semibold" style={{ backgroundColor: c(0), color: readableOn(c(0)) }}>Featured</div>
                                <p className="p-4 text-sm text-muted-foreground">A card headed with your dominant color.</p>
                            </div>
                        )}
                        {colors[1] && (
                            <div className="rounded-md border border-border p-4" style={{ borderLeft: `4px solid ${c(1)}` }}>
                                <h4 className="mb-1 text-sm font-semibold">Accent edge</h4>
                                <p className="text-sm text-muted-foreground">A left rule in your second color.</p>
                            </div>
                        )}
                        {colors[2] && (
                            <div className="rounded-md border border-border p-4" style={{ backgroundColor: `${c(2)}14` }}>
                                <h4 className="mb-1 text-sm font-semibold">Tinted</h4>
                                <p className="text-sm text-muted-foreground">A wash of your third color at 8%.</p>
                            </div>
                        )}
                    </div>
                </div>

                <div className="space-y-2.5">
                    <p className="font-mono text-[11px] text-muted-foreground">Badges</p>
                    <div className="flex flex-wrap gap-2">
                        {colors.slice(0, 8).map((col, i) => (
                            <span key={i} className="rounded-full px-3 py-1 text-xs font-semibold" style={{ backgroundColor: col.hex, color: readableOn(col.hex) }}>
                                {col.hex}
                            </span>
                        ))}
                    </div>
                </div>
            </Card>
        </section>
    );
}

// ---- 02 · Generate ---------------------------------------------------------
const SCHEMES = [
    { value: 'monochromatic', label: 'Monochromatic', desc: 'One hue, varied lightness', count: true },
    { value: 'complementary', label: 'Complementary', desc: 'Opposite on the wheel' },
    { value: 'analogous', label: 'Analogous', desc: 'Neighbouring hues' },
    { value: 'triadic', label: 'Triadic', desc: 'Three, evenly spaced' },
    { value: 'tetradic', label: 'Tetradic', desc: 'Two complementary pairs' },
    { value: 'split-complementary', label: 'Split Complementary', desc: 'Either side of the complement' },
    { value: 'shades', label: 'Shades', desc: 'Toward black', count: true },
    { value: 'tints', label: 'Tints', desc: 'Toward white', count: true },
    { value: 'pastel', label: 'Pastel', desc: 'Soft & desaturated' },
    { value: 'vibrant', label: 'Vibrant', desc: 'Bold & saturated' },
    { value: 'website-theme', label: 'Website Theme', desc: 'Full UI role set' },
];

function PaletteGenerator() {
    const [base, setBase] = useState('#e2532a');
    const [scheme, setScheme] = useState('analogous');
    const [count, setCount] = useState(5);
    const [loading, setLoading] = useState(false);
    const [palette, setPalette] = useState([]);
    const { copy, copied } = useCopy();
    const showCount = SCHEMES.find(s => s.value === scheme)?.count;

    const generate = async () => {
        setLoading(true); setPalette([]);
        try {
            const res = await fetch('api.php?action=generate', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ color: base, scheme, count }),
            });
            const data = await res.json();
            if (data.success) setPalette(data.colors);
        } finally { setLoading(false); }
    };
    useEffect(() => { generate(); /* eslint-disable-next-line */ }, []);

    return (
        <div className="grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,340px)_1fr]">
            <div className="space-y-5">
                <HexField label="Base color" value={base} onChange={setBase} />

                <div className="space-y-1.5">
                    <span className="eyebrow text-muted-foreground">Harmony</span>
                    <div className="grid grid-cols-1 gap-1.5">
                        {SCHEMES.map(s => (
                            <button
                                key={s.value}
                                onClick={() => setScheme(s.value)}
                                className={`flex items-baseline justify-between rounded-md border px-3 py-2 text-left transition ${scheme === s.value ? 'border-accent bg-accent/5' : 'border-border hover:bg-muted'}`}
                            >
                                <span className="text-sm font-semibold">{s.label}</span>
                                <span className="ml-3 hidden font-mono text-[10px] text-muted-foreground sm:block">{s.desc}</span>
                            </button>
                        ))}
                    </div>
                </div>

                {showCount && (
                    <div className="space-y-1.5">
                        <div className="flex items-baseline justify-between">
                            <span className="eyebrow text-muted-foreground">Count</span>
                            <span className="font-mono text-sm">{count}</span>
                        </div>
                        <input type="range" min="3" max="10" value={count} onChange={(e) => setCount(+e.target.value)} className="h-1.5 w-full cursor-pointer" />
                    </div>
                )}

                <AccentButton onClick={generate} disabled={loading} className="h-11 w-full">
                    {loading ? <><Spinner className="h-4 w-4" /> Generating…</> : 'Generate'}
                </AccentButton>
            </div>

            <div className="space-y-3">
                <Eyebrow>{SCHEMES.find(s => s.value === scheme)?.label} · {palette.length}</Eyebrow>
                {palette.length > 0 ? (
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5">
                        {palette.map((c, i) => (
                            <Swatch key={`${scheme}-${i}`} hex={c.hex} index={i} sub={c.isLight ? 'light' : 'dark'} onCopy={copy} copied={copied} />
                        ))}
                    </div>
                ) : (
                    <div className="grid h-48 place-items-center rounded-lg border border-dashed border-border">
                        <p className="font-mono text-xs text-muted-foreground">Pick a base color and harmony.</p>
                    </div>
                )}
            </div>
        </div>
    );
}

// ---- 03 · Manipulate -------------------------------------------------------
const OPS = [
    { value: 'lighten', label: 'Lighten', min: 0, max: 1, step: 0.05, unit: '' },
    { value: 'darken', label: 'Darken', min: 0, max: 1, step: 0.05, unit: '' },
    { value: 'saturate', label: 'Saturate', min: 0, max: 1, step: 0.05, unit: '' },
    { value: 'desaturate', label: 'Desaturate', min: 0, max: 1, step: 0.05, unit: '' },
    { value: 'rotate', label: 'Rotate hue', min: -180, max: 180, step: 15, unit: '°' },
];

function ColorManipulator() {
    const [base, setBase] = useState('#2b6cb0');
    const [op, setOp] = useState('lighten');
    const [amount, setAmount] = useState(0.25);
    const [result, setResult] = useState(null);
    const { copy, copied } = useCopy();
    const current = OPS.find(o => o.value === op);

    useEffect(() => {
        let alive = true;
        (async () => {
            try {
                const res = await fetch('api.php?action=manipulate', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ color: base, operation: op, amount }),
                });
                const data = await res.json();
                if (alive && data.success) setResult(data);
            } catch (_) { /* ignore */ }
        })();
        return () => { alive = false; };
    }, [base, op, amount]);

    const Panel = ({ label, c }) => (
        <div className="space-y-2">
            <Eyebrow>{label}</Eyebrow>
            <button onClick={() => copy(c.hex)} className="swatch block h-28 w-full rounded-lg border border-border" style={{ backgroundColor: c.hex }} title={`Copy ${c.hex}`}>
                <span className="text-xs font-semibold" style={{ color: readableOn(c.hex) }}>{copied === c.hex ? 'Copied' : ''}</span>
            </button>
            <dl className="space-y-0.5 font-mono text-xs">
                <Row k="HEX" v={c.hex.toUpperCase()} />
                <Row k="RGB" v={`${c.rgb.r} ${c.rgb.g} ${c.rgb.b}`} />
                <Row k="HSL" v={`${Math.round(c.hsl.h)}° ${Math.round(c.hsl.s)}% ${Math.round(c.hsl.l)}%`} />
            </dl>
        </div>
    );

    return (
        <div className="grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,340px)_1fr]">
            <div className="space-y-5">
                <HexField label="Base color" value={base} onChange={setBase} />
                <div className="space-y-1.5">
                    <span className="eyebrow text-muted-foreground">Operation</span>
                    <div className="grid grid-cols-2 gap-1.5">
                        {OPS.map(o => (
                            <button
                                key={o.value}
                                onClick={() => { setOp(o.value); setAmount(o.value === 'rotate' ? 45 : 0.25); }}
                                className={`rounded-md border px-3 py-2 text-sm font-semibold transition ${op === o.value ? 'border-accent bg-accent/5' : 'border-border hover:bg-muted'}`}
                            >
                                {o.label}
                            </button>
                        ))}
                    </div>
                </div>
                <div className="space-y-1.5">
                    <div className="flex items-baseline justify-between">
                        <span className="eyebrow text-muted-foreground">Amount</span>
                        <span className="font-mono text-sm">{amount}{current.unit}</span>
                    </div>
                    <input type="range" min={current.min} max={current.max} step={current.step} value={amount} onChange={(e) => setAmount(parseFloat(e.target.value))} className="h-1.5 w-full cursor-pointer" />
                </div>
            </div>

            {result && (
                <div className="grid grid-cols-2 gap-6">
                    <Panel label="Before" c={result.original} />
                    <Panel label={`After · ${current.label}`} c={result.result} />
                </div>
            )}
        </div>
    );
}

function Row({ k, v }) {
    return (
        <div className="flex justify-between gap-4">
            <dt className="text-muted-foreground">{k}</dt>
            <dd className="font-medium">{v}</dd>
        </div>
    );
}

// ---- 04 · Contrast ---------------------------------------------------------
function AccessibilityChecker() {
    const [bg, setBg] = useState('#fbfaf6');
    const [fg, setFg] = useState('#e2532a');
    const [result, setResult] = useState(null);

    useEffect(() => {
        let alive = true;
        (async () => {
            try {
                const res = await fetch('api.php?action=contrast', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ background: bg, text: fg }),
                });
                const data = await res.json();
                if (alive && data.success) setResult(data);
            } catch (_) { /* ignore */ }
        })();
        return () => { alive = false; };
    }, [bg, fg]);

    return (
        <div className="grid grid-cols-1 gap-8 lg:grid-cols-[minmax(0,340px)_1fr]">
            <div className="space-y-5">
                <HexField label="Background" value={bg} onChange={setBg} />
                <HexField label="Text" value={fg} onChange={setFg} />
                {result && !result.wcag.aa.normal && (
                    <div className="rounded-md border border-accent/40 bg-accent/5 p-4">
                        <p className="text-sm font-semibold">Below AA for body text</p>
                        <p className="mt-1 text-xs text-muted-foreground">Nearest readable text color:</p>
                        <button onClick={() => setFg(result.suggestedTextColor.hex)} className="mt-3 inline-flex h-9 items-center gap-2 rounded-md border border-border px-3 font-mono text-xs transition hover:bg-muted">
                            <span className="h-4 w-4 rounded-[3px] border border-border" style={{ backgroundColor: result.suggestedTextColor.hex }} />
                            Use {result.suggestedTextColor.hex}
                        </button>
                    </div>
                )}
            </div>

            {result && (
                <div className="space-y-6">
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-[auto_1fr] sm:items-center">
                        <div>
                            <Eyebrow>Contrast ratio</Eyebrow>
                            <p className="font-display text-6xl font-semibold leading-none tracking-tight">
                                {result.contrastRatio}<span className="text-2xl text-muted-foreground">:1</span>
                            </p>
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <Grade title="AA" rows={[['Body', result.wcag.aa.normal, '4.5'], ['Large', result.wcag.aa.large, '3.0']]} />
                            <Grade title="AAA" rows={[['Body', result.wcag.aaa.normal, '7.0'], ['Large', result.wcag.aaa.large, '4.5']]} />
                        </div>
                    </div>

                    <div className="rounded-lg border border-border p-8" style={{ backgroundColor: bg, color: fg }}>
                        <p className="font-display text-3xl font-semibold">The quick brown fox</p>
                        <p className="mt-3 text-base">Jumps over the lazy dog. This is how body copy reads at the chosen contrast — adjust the colors until both pass AA.</p>
                        <p className="mt-2 text-sm opacity-90">Small print: lorem ipsum dolor sit amet, consectetur adipiscing.</p>
                    </div>
                </div>
            )}
        </div>
    );
}

function Grade({ title, rows }) {
    return (
        <div className="rounded-md border border-border p-3">
            <p className="eyebrow mb-2">WCAG {title}</p>
            <div className="space-y-1.5">
                {rows.map(([label, pass, req]) => (
                    <div key={label} className="flex items-center justify-between text-sm">
                        <span className="text-muted-foreground">{label}</span>
                        <span className="flex items-center gap-2">
                            <span className="font-mono text-[10px] text-muted-foreground">{req}</span>
                            {pass ? (
                                <svg className="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" /></svg>
                            ) : (
                                <svg className="h-4 w-4 text-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5"><path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            )}
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
