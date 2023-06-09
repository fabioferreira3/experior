const defaultTheme = require("tailwindcss/defaultTheme");
const colors = require("tailwindcss/colors");

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./src/**/*.{js,ts,jsx,tsx}",
        "./app/Http/Livewire/**/*Table.php",
        "./vendor/rappasoft/laravel-livewire-tables/resources/views/**/*.blade.php",
        "./vendor/wireui/wireui/resources/**/*.blade.php",
        "./vendor/wireui/wireui/ts/**/*.ts",
        "./vendor/wireui/wireui/src/View/**/*.php",
    ],
    safelist: ["bg-red-200", "bg-blue-200"],
    theme: {
        fontFamily: {
            sans: ["Avenir Regular", ...defaultTheme.fontFamily.sans],
            bold: ['"Avenir Black"'],
            thin: ['"Avenir Light"'],
        },
        extend: {
            colors: {
                primary: "#080B53",
                secondary: "#EA1F88",
                positive: colors.emerald,
                negative: colors.red,
                warning: colors.amber,
                info: colors.blue,
            },
        },
    },

    plugins: [
        require("@tailwindcss/forms"),
        require("@tailwindcss/typography"),
    ],

    presets: [require("./vendor/wireui/wireui/tailwind.config.js")],
};
