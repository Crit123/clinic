tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            "colors": {
                // Primary (Deepened from Logo Blue)
                "primary": "#05449E",
                "on-primary": "#FFFFFF",
                "primary-container": "#D6E4FF",
                "on-primary-container": "#001844",
                "primary-fixed": "#D6E4FF",
                "on-primary-fixed": "#001844",
                "primary-fixed-dim": "#A8C7FF",
                "on-primary-fixed-variant": "#00347B",
                "surface-tint": "#05449E",
                "inverse-primary": "#A8C7FF",

                // Secondary (Cyan from Logo Swoosh/Star)
                "secondary": "#00B4D8",
                "on-secondary": "#FFFFFF",
                "secondary-container": "#C1F4FF",
                "on-secondary-container": "#001F29",
                "secondary-fixed": "#C1F4FF",
                "on-secondary-fixed": "#001F29",
                "secondary-fixed-dim": "#70D5ED",
                "on-secondary-fixed-variant": "#008EA9",

                // Tertiary (Bridging tone)
                "tertiary": "#007799",
                "on-tertiary": "#FFFFFF",
                "tertiary-container": "#C1F4FF",
                "on-tertiary-container": "#001F29",
                "tertiary-fixed": "#C1F4FF",
                "on-tertiary-fixed": "#001F29",
                "tertiary-fixed-dim": "#66D3EE",
                "on-tertiary-fixed-variant": "#005A75",

                // Accent (New Warm Tone for AI / CTAs)
                "accent-warm": "#E85D04",
                "on-accent-warm": "#FFFFFF",
                "accent-warm-container": "#FFDBCF",
                "on-accent-warm-container": "#3B0900",

                // Background & Surface (Warm Paper Tones)
                "background": "#FBF9F6",
                "on-background": "#1F1C18",
                "surface": "#FBF9F6",
                "on-surface": "#1F1C18",
                "surface-variant": "#E4DBCD",
                "on-surface-variant": "#4E4A44",
                "surface-container-lowest": "#FFFFFF",
                "surface-container-low": "#F5F2EB",
                "surface-container": "#F0EBE1",
                "surface-container-high": "#EBE3D7",
                "surface-container-highest": "#E4DBCD",
                "surface-bright": "#FBF9F6",
                "surface-dim": "#E4DBCD",
                "inverse-surface": "#34302A",
                "inverse-on-surface": "#F6F0E9",

                // Outlines & Errors
                "outline": "#7F7972",
                "outline-variant": "#D0C8BF",
                "error": "#BA1A1A",
                "on-error": "#FFFFFF",
                "error-container": "#FFDAD6",
                "on-error-container": "#410002"
            },
            "borderRadius": {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "2xl": "1rem",
                "3xl": "1.5rem",
                "full": "9999px"
            },
            "spacing": {
                "gutter": "24px",
                "md": "24px",
                "margin-mobile": "16px",
                "xs": "4px",
                "lg": "48px",
                "xl": "80px",
                "margin-desktop": "64px",
                "sm": "12px",
                "base": "8px"
            },
            "fontFamily": {
                "sans": ["\"Plus Jakarta Sans\"", "sans-serif"],
                "display": ["\"Fraunces\"", "serif"],
                "mono": ["\"Roboto Mono\"", "monospace"],
                
                // Existing utility class mappings transitioned to new system
                "label-sm": ["\"Plus Jakarta Sans\"", "sans-serif"],
                "body-md": ["\"Plus Jakarta Sans\"", "sans-serif"],
                "body-lg": ["\"Plus Jakarta Sans\"", "sans-serif"],
                "label-md": ["\"Plus Jakarta Sans\"", "sans-serif"],
                "headline-md": ["\"Fraunces\"", "serif"],
                "headline-xl": ["\"Fraunces\"", "serif"],
                "headline-lg-mobile": ["\"Fraunces\"", "serif"],
                "headline-lg": ["\"Fraunces\"", "serif"]
            },
            "animation": {
                "float": "float 6s ease-in-out infinite",
                "float-delayed": "float 6s ease-in-out 3s infinite",
                "pulse-slow": "pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite",
                "bounce-slow": "bounce 3s infinite"
            },
            "keyframes": {
                float: {
                    "0%, 100%": { transform: "translateY(0px)" },
                    "50%": { transform: "translateY(-15px)" }
                }
            }
        }
    }
};