const { useState, useEffect, useRef, createContext, useContext } = React;

// Theme Context
const ThemeContext = createContext();

function ThemeProvider({ children }) {
    const [theme, setTheme] = useState(() => {
        const saved = localStorage.getItem('theme');
        if (saved) return saved;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    });

    useEffect(() => {
        const root = document.documentElement;
        root.classList.remove('light', 'dark');
        root.classList.add(theme);
        localStorage.setItem('theme', theme);
    }, [theme]);

    const toggleTheme = () => {
        setTheme(prev => prev === 'light' ? 'dark' : 'light');
    };

    return (
        <ThemeContext.Provider value={{ theme, toggleTheme }}>
            {children}
        </ThemeContext.Provider>
    );
}

function useTheme() {
    const context = useContext(ThemeContext);
    if (!context) throw new Error('useTheme must be used within ThemeProvider');
    return context;
}

// Main App Component
function App() {
    return (
        <ThemeProvider>
            <AppContent />
        </ThemeProvider>
    );
}

function AppContent() {
    const [activeTab, setActiveTab] = useState('extract');
    const { theme, toggleTheme } = useTheme();

    return (
        <div className="min-h-screen bg-background">
            {/* Header */}
            <header className="sticky top-0 z-50 border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                <div className="container mx-auto px-4 py-4 sm:px-6 lg:px-8">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center space-x-4">
                            <div className="flex items-center space-x-2">
                                <div className="h-8 w-8 rounded-md bg-gradient-to-br from-violet-600 to-indigo-600"></div>
                                <div>
                                    <h1 className="text-xl font-semibold text-foreground">Color Palette</h1>
                                    <p className="text-xs text-muted-foreground">Extract & Generate Colors</p>
                                </div>
                            </div>
                        </div>

                        {/* Dark Mode Toggle */}
                        <button
                            onClick={toggleTheme}
                            className="inline-flex items-center justify-center rounded-md h-9 w-9 hover:bg-accent hover:text-accent-foreground transition-colors"
                            aria-label="Toggle theme"
                        >
                            {theme === 'light' ? (
                                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                </svg>
                            ) : (
                                <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            )}
                        </button>
                    </div>
                </div>
            </header>

            {/* Tab Navigation */}
            <div className="container mx-auto px-4 sm:px-6 lg:px-8 mt-6">
                <div className="inline-flex h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground">
                    <TabButton
                        active={activeTab === 'extract'}
                        onClick={() => setActiveTab('extract')}
                        label="Extract"
                    />
                    <TabButton
                        active={activeTab === 'generate'}
                        onClick={() => setActiveTab('generate')}
                        label="Generate"
                    />
                    <TabButton
                        active={activeTab === 'manipulate'}
                        onClick={() => setActiveTab('manipulate')}
                        label="Manipulate"
                    />
                    <TabButton
                        active={activeTab === 'accessibility'}
                        onClick={() => setActiveTab('accessibility')}
                        label="Accessibility"
                    />
                </div>

                {/* Tab Content */}
                <div className="mt-6 mb-12 animate-fade-in">
                    {activeTab === 'extract' && <ImageExtractor />}
                    {activeTab === 'generate' && <PaletteGenerator />}
                    {activeTab === 'manipulate' && <ColorManipulator />}
                    {activeTab === 'accessibility' && <AccessibilityChecker />}
                </div>
            </div>
        </div>
    );
}

// Tab Button Component
function TabButton({ active, onClick, label }) {
    return (
        <button
            onClick={onClick}
            className={`inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 ${
                active
                    ? 'bg-background text-foreground shadow-sm'
                    : 'hover:bg-background/50 hover:text-foreground'
            }`}
        >
            {label}
        </button>
    );
}

// Image Extraction Component
function ImageExtractor() {
    const [image, setImage] = useState(null);
    const [imagePreview, setImagePreview] = useState(null);
    const [loading, setLoading] = useState(false);
    const [colors, setColors] = useState([]);
    const [theme, setTheme] = useState(null);
    const [colorCount, setColorCount] = useState(5);
    const [dragOver, setDragOver] = useState(false);
    const fileInputRef = useRef(null);

    const loadRandomImage = async () => {
        const timestamp = Date.now();
        const randomUrl = `https://picsum.photos/800/600?random=${timestamp}`;
        setImagePreview(randomUrl);
        setImage({ type: 'url', url: randomUrl });
        extractColors({ type: 'url', url: randomUrl }, colorCount);
    };

    const handleFileSelect = (e) => {
        const file = e.target.files[0];
        if (file) processFile(file);
    };

    const handleDrop = (e) => {
        e.preventDefault();
        setDragOver(false);
        const file = e.dataTransfer.files[0];
        if (file) processFile(file);
    };

    const processFile = (file) => {
        if (!file.type.startsWith('image/')) {
            alert('Please upload an image file');
            return;
        }

        // Check file size (10MB limit)
        const maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if (file.size > maxSize) {
            alert(`File is too large (${(file.size / 1024 / 1024).toFixed(2)}MB). Maximum size is 10MB.`);
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            setImagePreview(e.target.result);
            setImage({ type: 'file', file });
        };
        reader.readAsDataURL(file);
    };

    const extractColors = async (imageSource, count) => {
        setLoading(true);
        setColors([]);
        setTheme(null);

        try {
            const formData = new FormData();
            if (imageSource.type === 'url') {
                formData.append('url', imageSource.url);
            } else {
                formData.append('image', imageSource.file);
            }
            formData.append('count', count);

            const response = await fetch('api.php?action=extract', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                setColors(data.colors);
                setTheme(data.theme);
            } else {
                alert('Error: ' + data.error);
            }
        } catch (error) {
            alert('Error extracting colors: ' + error.message);
        } finally {
            setLoading(false);
        }
    };

    const handleExtract = () => {
        if (image) extractColors(image, colorCount);
    };

    const copyToClipboard = (text) => {
        navigator.clipboard.writeText(text);
    };

    const exportAsJSON = () => {
        const data = {
            colors: colors.map(c => c.hex),
            theme: theme ? Object.fromEntries(Object.entries(theme).map(([k, v]) => [k, v.hex])) : null
        };
        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'color-palette.json';
        a.click();
    };

    const exportAsCSS = () => {
        let css = ':root {\n';
        colors.forEach((color, i) => {
            css += `  --color-${i + 1}: ${color.hex};\n`;
        });
        if (theme) {
            Object.entries(theme).forEach(([key, value]) => {
                css += `  --${key}: ${value.hex};\n`;
            });
        }
        css += '}\n';

        const blob = new Blob([css], { type: 'text/css' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'color-palette.css';
        a.click();
    };

    return (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Left Column - Upload */}
            <div className="space-y-4">
                <div className="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                    <div className="p-4 space-y-3">
                        {/* Header with Color Count Slider */}
                        <div className="space-y-2">
                            <div className="flex items-center justify-between gap-4">
                                <h2 className="text-lg font-semibold whitespace-nowrap">Upload Image</h2>
                                <div className="flex items-center gap-2 flex-1 min-w-0">
                                    <span className="text-xs text-muted-foreground whitespace-nowrap">Colors: {colorCount}</span>
                                    <input
                                        type="range"
                                        min="3"
                                        max="15"
                                        value={colorCount}
                                        onChange={(e) => setColorCount(parseInt(e.target.value))}
                                        className="flex-1 h-2 bg-secondary rounded-lg appearance-none cursor-pointer accent-primary"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Drag & Drop Zone */}
                        <div
                            onDragOver={(e) => { e.preventDefault(); setDragOver(true); }}
                            onDragLeave={() => setDragOver(false)}
                            onDrop={handleDrop}
                            onClick={() => fileInputRef.current.click()}
                            className={`upload-zone border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-all ${
                                dragOver ? 'drag-over' : 'border-border hover:border-muted-foreground/50'
                            }`}
                        >
                            <svg className="mx-auto h-8 w-8 text-muted-foreground mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p className="text-sm font-medium mb-0.5">Click to upload or drag and drop</p>
                            <p className="text-xs text-muted-foreground">PNG, JPG, GIF, WebP (Max 10MB)</p>
                            <input
                                ref={fileInputRef}
                                type="file"
                                accept="image/*"
                                onChange={handleFileSelect}
                                className="hidden"
                            />
                        </div>

                        {/* Image Preview */}
                        {imagePreview && (
                            <div className="overflow-hidden rounded-lg border border-border">
                                <img src={imagePreview} alt="Preview" className="w-full" />
                            </div>
                        )}

                        {/* Action Buttons */}
                        <div className="flex gap-2">
                            <button
                                onClick={loadRandomImage}
                                className="inline-flex items-center justify-center rounded-md text-sm font-medium h-10 px-3 border border-input bg-background hover:bg-accent hover:text-accent-foreground transition-colors"
                            >
                                <svg className="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Random Demo
                            </button>
                            <button
                                onClick={handleExtract}
                                disabled={!image || loading}
                                className="flex-1 inline-flex items-center justify-center rounded-md text-sm font-medium h-10 px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 disabled:opacity-50 disabled:pointer-events-none transition-colors"
                            >
                                {loading ? (
                                    <>
                                        <svg className="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Extracting...
                                    </>
                                ) : (
                                    'Extract Colors'
                                )}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Right Column - Results */}
            <div className="space-y-4">
                {colors.length > 0 && (
                    <>
                        {/* Colors and Theme Colors - Side by Side */}
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            {/* Extracted Colors */}
                            <div className="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                                <div className="p-6">
                                    <div className="flex items-center justify-between mb-4">
                                        <h2 className="text-lg font-semibold">Colors ({colors.length})</h2>
                                        <div className="flex gap-2">
                                            <button
                                                onClick={exportAsJSON}
                                                className="inline-flex items-center justify-center rounded-md text-xs font-medium h-8 px-3 border border-border bg-background hover:bg-accent hover:text-accent-foreground transition-colors"
                                            >
                                                JSON
                                            </button>
                                            <button
                                                onClick={exportAsCSS}
                                                className="inline-flex items-center justify-center rounded-md text-xs font-medium h-8 px-3 border border-border bg-background hover:bg-accent hover:text-accent-foreground transition-colors"
                                            >
                                                CSS
                                            </button>
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                        {colors.map((color, index) => (
                                            <ColorSwatch key={index} color={color} onClick={() => copyToClipboard(color.hex)} />
                                        ))}
                                    </div>
                                </div>
                            </div>

                            {/* Theme Colors */}
                            {theme && (
                                <div className="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                                    <div className="p-6">
                                        <h2 className="text-lg font-semibold mb-4">Theme Colors</h2>
                                        <div className="space-y-3">
                                            {Object.entries(theme).map(([key, value]) => (
                                                <div key={key} className="flex items-center gap-3 group">
                                                    <div
                                                        className="w-12 h-12 rounded-md shadow-sm cursor-pointer color-swatch border border-border"
                                                        style={{ backgroundColor: value.hex }}
                                                        onClick={() => copyToClipboard(value.hex)}
                                                    ></div>
                                                    <div className="flex-1">
                                                        <p className="text-sm font-medium capitalize">{key.replace('_', ' ')}</p>
                                                        <p className="text-xs text-muted-foreground font-mono">{value.hex}</p>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Component Examples */}
                        <ComponentShowcase colors={colors} />
                    </>
                )}
            </div>
        </div>
    );
}

// Component Showcase - demonstrates colors in UI components
function ComponentShowcase({ colors }) {
    if (!colors || colors.length === 0) return null;

    // Helper to determine if we should use white or black text based on background
    const getTextColor = (hex) => {
        // Simple luminance calculation
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return luminance > 0.5 ? '#000000' : '#ffffff';
    };

    return (
        <div className="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
            <div className="p-6 space-y-6">
                <div>
                    <h2 className="text-lg font-semibold mb-1">Component Examples</h2>
                    <p className="text-sm text-muted-foreground">See your colors in action</p>
                </div>

                {/* Buttons Section */}
                <div className="space-y-3">
                    <h3 className="text-sm font-medium text-muted-foreground">Buttons</h3>
                    <div className="flex flex-wrap gap-3">
                        {/* Primary Button */}
                        {colors[0] && (
                            <button
                                className="inline-flex items-center justify-center rounded-md text-sm font-medium h-10 px-4 py-2 transition-all hover:opacity-90"
                                style={{
                                    backgroundColor: colors[0].hex,
                                    color: getTextColor(colors[0].hex)
                                }}
                            >
                                Primary Button
                            </button>
                        )}

                        {/* Secondary Button */}
                        {colors[1] && (
                            <button
                                className="inline-flex items-center justify-center rounded-md text-sm font-medium h-10 px-4 py-2 transition-all hover:opacity-90"
                                style={{
                                    backgroundColor: colors[1].hex,
                                    color: getTextColor(colors[1].hex)
                                }}
                            >
                                Secondary Button
                            </button>
                        )}

                        {/* Outline Button */}
                        {colors[2] && (
                            <button
                                className="inline-flex items-center justify-center rounded-md text-sm font-medium h-10 px-4 py-2 bg-background transition-all hover:opacity-80"
                                style={{
                                    borderWidth: '2px',
                                    borderColor: colors[2].hex,
                                    color: colors[2].hex
                                }}
                            >
                                Outline Button
                            </button>
                        )}
                    </div>
                </div>

                {/* Cards Section */}
                <div className="space-y-3">
                    <h3 className="text-sm font-medium text-muted-foreground">Cards</h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        {/* Card with colored header */}
                        {colors[0] && (
                            <div className="rounded-lg border border-border overflow-hidden bg-background">
                                <div
                                    className="p-4"
                                    style={{
                                        backgroundColor: colors[0].hex,
                                        color: getTextColor(colors[0].hex)
                                    }}
                                >
                                    <h4 className="font-semibold">Featured Card</h4>
                                </div>
                                <div className="p-4">
                                    <p className="text-sm text-muted-foreground">Card with colored header using your first color.</p>
                                </div>
                            </div>
                        )}

                        {/* Card with accent border */}
                        {colors[1] && (
                            <div
                                className="rounded-lg border-l-4 p-4 bg-background"
                                style={{
                                    borderLeftColor: colors[1].hex,
                                    borderTop: '1px solid hsl(var(--border))',
                                    borderRight: '1px solid hsl(var(--border))',
                                    borderBottom: '1px solid hsl(var(--border))'
                                }}
                            >
                                <h4 className="font-semibold mb-2">Accent Card</h4>
                                <p className="text-sm text-muted-foreground">Card with left accent border using your second color.</p>
                            </div>
                        )}

                        {/* Card with subtle background */}
                        {colors[2] && (
                            <div
                                className="rounded-lg border border-border p-4"
                                style={{
                                    backgroundColor: `${colors[2].hex}15`
                                }}
                            >
                                <h4 className="font-semibold mb-2">Tinted Card</h4>
                                <p className="text-sm text-muted-foreground">Card with subtle background tint using your third color.</p>
                            </div>
                        )}
                    </div>
                </div>

                {/* Alerts Section */}
                <div className="space-y-3">
                    <h3 className="text-sm font-medium text-muted-foreground">Alerts</h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                        {colors.slice(0, 4).map((color, index) => {
                            const alertTypes = ['Info', 'Success', 'Warning', 'Error'];
                            const alertIcons = [
                                // Info icon
                                <svg key="info" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>,
                                // Success icon
                                <svg key="success" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>,
                                // Warning icon
                                <svg key="warning" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>,
                                // Error icon
                                <svg key="error" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            ];

                            return (
                                <div
                                    key={index}
                                    className="rounded-lg p-4 flex items-start gap-3"
                                    style={{
                                        backgroundColor: `${color.hex}15`,
                                        borderLeft: `4px solid ${color.hex}`
                                    }}
                                >
                                    <div style={{ color: color.hex }}>
                                        {alertIcons[index]}
                                    </div>
                                    <div className="flex-1">
                                        <h4 className="font-semibold text-sm mb-1">{alertTypes[index]} Alert</h4>
                                        <p className="text-xs text-muted-foreground">This is an example {alertTypes[index].toLowerCase()} message using color {index + 1}.</p>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* Badges Section */}
                <div className="space-y-3">
                    <h3 className="text-sm font-medium text-muted-foreground">Badges</h3>
                    <div className="flex flex-wrap gap-2">
                        {colors.slice(0, 6).map((color, index) => (
                            <span
                                key={index}
                                className="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium transition-all"
                                style={{
                                    backgroundColor: color.hex,
                                    color: getTextColor(color.hex)
                                }}
                            >
                                Badge {index + 1}
                            </span>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}

// Palette Generator Component
function PaletteGenerator() {
    const [baseColor, setBaseColor] = useState('#6366f1');
    const [scheme, setScheme] = useState('monochromatic');
    const [colorCount, setColorCount] = useState(5);
    const [loading, setLoading] = useState(false);
    const [palette, setPalette] = useState([]);

    const schemes = [
        { value: 'monochromatic', label: 'Monochromatic', desc: 'Same hue, different lightness' },
        { value: 'complementary', label: 'Complementary', desc: 'Opposite colors' },
        { value: 'analogous', label: 'Analogous', desc: 'Adjacent colors' },
        { value: 'triadic', label: 'Triadic', desc: 'Evenly spaced' },
        { value: 'tetradic', label: 'Tetradic', desc: 'Four colors' },
        { value: 'split-complementary', label: 'Split Complementary', desc: 'Adjacent to complement' },
        { value: 'shades', label: 'Shades', desc: 'Add black' },
        { value: 'tints', label: 'Tints', desc: 'Add white' },
        { value: 'pastel', label: 'Pastel', desc: 'Soft colors' },
        { value: 'vibrant', label: 'Vibrant', desc: 'Saturated' },
        { value: 'website-theme', label: 'Website Theme', desc: 'UI theme' }
    ];

    const generatePalette = async () => {
        setLoading(true);
        setPalette([]);

        try {
            const response = await fetch('api.php?action=generate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ color: baseColor, scheme: scheme, count: colorCount })
            });

            const data = await response.json();
            if (data.success) {
                setPalette(data.colors);
            } else {
                alert('Error: ' + data.error);
            }
        } catch (error) {
            alert('Error generating palette: ' + error.message);
        } finally {
            setLoading(false);
        }
    };

    const copyToClipboard = (text) => {
        navigator.clipboard.writeText(text);
    };

    return (
        <div className="max-w-4xl mx-auto">
            <div className="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                <div className="p-6 space-y-6">
                    <h2 className="text-xl font-semibold">Generate Palette</h2>

                    {/* Base Color Picker */}
                    <div className="space-y-2">
                        <label className="text-sm font-medium">Base Color</label>
                        <div className="flex gap-3">
                            <input
                                type="color"
                                value={baseColor}
                                onChange={(e) => setBaseColor(e.target.value)}
                                className="h-10 w-16 rounded-md cursor-pointer border border-border"
                            />
                            <input
                                type="text"
                                value={baseColor}
                                onChange={(e) => setBaseColor(e.target.value)}
                                className="flex-1 h-10 rounded-md border border-input bg-background px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            />
                        </div>
                    </div>

                    {/* Scheme Selector */}
                    <div className="space-y-2">
                        <label className="text-sm font-medium">Color Scheme</label>
                        <select
                            value={scheme}
                            onChange={(e) => setScheme(e.target.value)}
                            className="w-full h-10 rounded-md border border-input bg-background px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                        >
                            {schemes.map(s => (
                                <option key={s.value} value={s.value}>
                                    {s.label} - {s.desc}
                                </option>
                            ))}
                        </select>
                    </div>

                    {/* Color Count */}
                    {['monochromatic', 'shades', 'tints'].includes(scheme) && (
                        <div className="space-y-2">
                            <div className="flex items-center justify-between">
                                <label className="text-sm font-medium">Color Count</label>
                                <span className="text-sm text-muted-foreground">{colorCount}</span>
                            </div>
                            <input
                                type="range"
                                min="3"
                                max="10"
                                value={colorCount}
                                onChange={(e) => setColorCount(parseInt(e.target.value))}
                                className="w-full h-2 bg-secondary rounded-lg appearance-none cursor-pointer accent-primary"
                            />
                        </div>
                    )}

                    {/* Generate Button */}
                    <button
                        onClick={generatePalette}
                        disabled={loading}
                        className="w-full inline-flex items-center justify-center rounded-md text-sm font-medium h-10 px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90 disabled:opacity-50 disabled:pointer-events-none transition-colors"
                    >
                        {loading ? 'Generating...' : 'Generate Palette'}
                    </button>

                    {/* Generated Palette */}
                    {palette.length > 0 && (
                        <div className="pt-4 border-t border-border">
                            <h3 className="text-sm font-medium mb-4">Generated Palette ({palette.length} colors)</h3>
                            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                                {palette.map((color, index) => (
                                    <ColorSwatch key={index} color={color} onClick={() => copyToClipboard(color.hex)} />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

// Color Manipulator Component
function ColorManipulator() {
    const [baseColor, setBaseColor] = useState('#6366f1');
    const [operation, setOperation] = useState('lighten');
    const [amount, setAmount] = useState(0.2);
    const [result, setResult] = useState(null);

    const operations = [
        { value: 'lighten', label: 'Lighten', min: 0, max: 1, step: 0.05 },
        { value: 'darken', label: 'Darken', min: 0, max: 1, step: 0.05 },
        { value: 'saturate', label: 'Saturate', min: 0, max: 1, step: 0.05 },
        { value: 'desaturate', label: 'Desaturate', min: 0, max: 1, step: 0.05 },
        { value: 'rotate', label: 'Rotate Hue', min: -180, max: 180, step: 15 }
    ];

    const currentOp = operations.find(op => op.value === operation);

    const manipulateColor = async () => {
        try {
            const response = await fetch('api.php?action=manipulate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ color: baseColor, operation: operation, amount: amount })
            });

            const data = await response.json();
            if (data.success) {
                setResult(data);
            }
        } catch (error) {
            console.error('Error manipulating color:', error);
        }
    };

    useEffect(() => {
        if (baseColor) manipulateColor();
    }, [baseColor, operation, amount]);

    const copyToClipboard = (text) => {
        navigator.clipboard.writeText(text);
    };

    return (
        <div className="max-w-4xl mx-auto">
            <div className="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                <div className="p-6 space-y-6">
                    <h2 className="text-xl font-semibold">Color Manipulation</h2>

                    {/* Base Color Picker */}
                    <div className="space-y-2">
                        <label className="text-sm font-medium">Base Color</label>
                        <div className="flex gap-3">
                            <input
                                type="color"
                                value={baseColor}
                                onChange={(e) => setBaseColor(e.target.value)}
                                className="h-10 w-16 rounded-md cursor-pointer border border-border"
                            />
                            <input
                                type="text"
                                value={baseColor}
                                onChange={(e) => setBaseColor(e.target.value)}
                                className="flex-1 h-10 rounded-md border border-input bg-background px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            />
                        </div>
                    </div>

                    {/* Operation Selector */}
                    <div className="space-y-2">
                        <label className="text-sm font-medium">Operation</label>
                        <div className="flex flex-wrap gap-2">
                            {operations.map(op => (
                                <button
                                    key={op.value}
                                    onClick={() => setOperation(op.value)}
                                    className={`inline-flex items-center justify-center rounded-md text-sm font-medium h-9 px-4 transition-colors ${
                                        operation === op.value
                                            ? 'bg-primary text-primary-foreground'
                                            : 'border border-input bg-background hover:bg-accent hover:text-accent-foreground'
                                    }`}
                                >
                                    {op.label}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Amount Slider */}
                    <div className="space-y-2">
                        <div className="flex items-center justify-between">
                            <label className="text-sm font-medium">Amount</label>
                            <span className="text-sm text-muted-foreground">{amount}{operation === 'rotate' ? '°' : ''}</span>
                        </div>
                        <input
                            type="range"
                            min={currentOp.min}
                            max={currentOp.max}
                            step={currentOp.step}
                            value={amount}
                            onChange={(e) => setAmount(parseFloat(e.target.value))}
                            className="w-full h-2 bg-secondary rounded-lg appearance-none cursor-pointer accent-primary"
                        />
                    </div>

                    {/* Before & After */}
                    {result && (
                        <div className="pt-4 border-t border-border">
                            <h3 className="text-sm font-medium mb-4">Result</h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {/* Original */}
                                <div className="space-y-3">
                                    <p className="text-sm font-medium text-muted-foreground">Original</p>
                                    <div
                                        className="w-full h-24 rounded-md shadow-sm cursor-pointer color-swatch border border-border"
                                        style={{ backgroundColor: result.original.hex }}
                                        onClick={() => copyToClipboard(result.original.hex)}
                                    ></div>
                                    <div className="space-y-1 text-xs">
                                        <p className="font-mono"><span className="text-muted-foreground">HEX:</span> {result.original.hex}</p>
                                        <p className="font-mono"><span className="text-muted-foreground">RGB:</span> {result.original.rgb.r}, {result.original.rgb.g}, {result.original.rgb.b}</p>
                                        <p className="font-mono"><span className="text-muted-foreground">HSL:</span> {Math.round(result.original.hsl.h)}°, {Math.round(result.original.hsl.s * 100)}%, {Math.round(result.original.hsl.l * 100)}%</p>
                                    </div>
                                </div>

                                {/* Result */}
                                <div className="space-y-3">
                                    <p className="text-sm font-medium text-muted-foreground">Result</p>
                                    <div
                                        className="w-full h-24 rounded-md shadow-sm cursor-pointer color-swatch border border-border"
                                        style={{ backgroundColor: result.result.hex }}
                                        onClick={() => copyToClipboard(result.result.hex)}
                                    ></div>
                                    <div className="space-y-1 text-xs">
                                        <p className="font-mono"><span className="text-muted-foreground">HEX:</span> {result.result.hex}</p>
                                        <p className="font-mono"><span className="text-muted-foreground">RGB:</span> {result.result.rgb.r}, {result.result.rgb.g}, {result.result.rgb.b}</p>
                                        <p className="font-mono"><span className="text-muted-foreground">HSL:</span> {Math.round(result.result.hsl.h)}°, {Math.round(result.result.hsl.s * 100)}%, {Math.round(result.result.hsl.l * 100)}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

// Accessibility Checker Component
function AccessibilityChecker() {
    const [backgroundColor, setBackgroundColor] = useState('#ffffff');
    const [textColor, setTextColor] = useState('#0f172a');
    const [result, setResult] = useState(null);

    const checkContrast = async () => {
        try {
            const response = await fetch('api.php?action=contrast', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ background: backgroundColor, text: textColor })
            });

            const data = await response.json();
            if (data.success) {
                setResult(data);
            }
        } catch (error) {
            console.error('Error checking contrast:', error);
        }
    };

    useEffect(() => {
        if (backgroundColor && textColor) checkContrast();
    }, [backgroundColor, textColor]);

    const useSuggestedColor = () => {
        if (result && result.suggestedTextColor) {
            setTextColor(result.suggestedTextColor.hex);
        }
    };

    return (
        <div className="max-w-4xl mx-auto">
            <div className="rounded-lg border border-border bg-card text-card-foreground shadow-sm">
                <div className="p-6 space-y-6">
                    <h2 className="text-xl font-semibold">Accessibility Checker</h2>

                    {/* Color Pickers */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {/* Background Color */}
                        <div className="space-y-2">
                            <label className="text-sm font-medium">Background</label>
                            <div className="flex gap-3">
                                <input
                                    type="color"
                                    value={backgroundColor}
                                    onChange={(e) => setBackgroundColor(e.target.value)}
                                    className="h-10 w-16 rounded-md cursor-pointer border border-border"
                                />
                                <input
                                    type="text"
                                    value={backgroundColor}
                                    onChange={(e) => setBackgroundColor(e.target.value)}
                                    className="flex-1 h-10 rounded-md border border-input bg-background px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                />
                            </div>
                        </div>

                        {/* Text Color */}
                        <div className="space-y-2">
                            <label className="text-sm font-medium">Text</label>
                            <div className="flex gap-3">
                                <input
                                    type="color"
                                    value={textColor}
                                    onChange={(e) => setTextColor(e.target.value)}
                                    className="h-10 w-16 rounded-md cursor-pointer border border-border"
                                />
                                <input
                                    type="text"
                                    value={textColor}
                                    onChange={(e) => setTextColor(e.target.value)}
                                    className="flex-1 h-10 rounded-md border border-input bg-background px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Results */}
                    {result && (
                        <div className="space-y-4">
                            {/* Contrast Ratio */}
                            <div className="rounded-lg bg-muted p-6 text-center">
                                <p className="text-sm text-muted-foreground mb-2">Contrast Ratio</p>
                                <p className="text-4xl font-bold">{result.contrastRatio}:1</p>
                            </div>

                            {/* WCAG Compliance */}
                            <div className="grid grid-cols-2 gap-4">
                                <div className="rounded-lg border border-border p-4 space-y-3">
                                    <h4 className="font-semibold text-sm">WCAG AA</h4>
                                    <ComplianceIndicator label="Normal" passed={result.wcag.aa.normal} required="4.5:1" />
                                    <ComplianceIndicator label="Large" passed={result.wcag.aa.large} required="3.0:1" />
                                </div>

                                <div className="rounded-lg border border-border p-4 space-y-3">
                                    <h4 className="font-semibold text-sm">WCAG AAA</h4>
                                    <ComplianceIndicator label="Normal" passed={result.wcag.aaa.normal} required="7.0:1" />
                                    <ComplianceIndicator label="Large" passed={result.wcag.aaa.large} required="4.5:1" />
                                </div>
                            </div>

                            {/* Preview */}
                            <div>
                                <h4 className="text-sm font-medium mb-3">Preview</h4>
                                <div
                                    className="p-6 rounded-lg border border-border"
                                    style={{ backgroundColor: backgroundColor, color: textColor }}
                                >
                                    <h1 className="text-2xl font-bold mb-2">The quick brown fox</h1>
                                    <p className="mb-2">
                                        The quick brown fox jumps over the lazy dog. This is a preview of how your text will look.
                                    </p>
                                    <p className="text-sm">
                                        Small text example: Lorem ipsum dolor sit amet.
                                    </p>
                                </div>
                            </div>

                            {/* Suggestion */}
                            {!result.wcag.aa.normal && (
                                <div className="rounded-lg border border-yellow-500/50 bg-yellow-500/10 p-4">
                                    <p className="font-medium text-sm mb-2">⚠️ Contrast is too low</p>
                                    <p className="text-sm text-muted-foreground mb-3">
                                        Consider using this color for better accessibility:
                                    </p>
                                    <button
                                        onClick={useSuggestedColor}
                                        className="inline-flex items-center justify-center rounded-md text-sm font-medium h-9 px-4 border border-input bg-background hover:bg-accent hover:text-accent-foreground transition-colors"
                                    >
                                        Use {result.suggestedTextColor.hex}
                                    </button>
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

// Compliance Indicator Component
function ComplianceIndicator({ label, passed, required }) {
    return (
        <div className="flex items-center justify-between text-sm">
            <span className="text-muted-foreground">{label}</span>
            <div className="flex items-center gap-2">
                <span className="text-xs text-muted-foreground">{required}</span>
                {passed ? (
                    <svg className="h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                    </svg>
                ) : (
                    <svg className="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                )}
            </div>
        </div>
    );
}

// Color Swatch Component
function ColorSwatch({ color, onClick }) {
    return (
        <div onClick={onClick} className="cursor-pointer color-swatch group">
            <div
                className="w-full h-20 rounded-t-md shadow-sm border border-border"
                style={{ backgroundColor: color.hex }}
            ></div>
            <div className="bg-muted/50 p-2 rounded-b-md border border-t-0 border-border">
                <p className="font-mono text-xs font-medium text-center">{color.hex}</p>
                <p className="text-[10px] text-muted-foreground text-center mt-0.5">
                    {color.isLight ? 'Light' : 'Dark'}
                </p>
            </div>
        </div>
    );
}

// Render the app
const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<App />);
