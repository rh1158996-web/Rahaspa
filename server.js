const express = require('express');
const session = require('express-session');
const fileUpload = require('express-fileupload');
const path = require('path');
const cors = require('cors');
const pool = require('./db');

const app = express();

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(fileUpload());

// Serve static assets from original directories
app.use('/css', express.static(path.join(__dirname, 'css')));
app.use('/js', express.static(path.join(__dirname, 'js')));
app.use('/admin/css', express.static(path.join(__dirname, 'admin/css')));
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// Session
app.use(session({
    secret: 'serenity_spa_secret_key',
    resave: false,
    saveUninitialized: true
}));

// Global Middleware (Init.php equivalent)
app.use(async (req, res, next) => {
    if (req.query.lang && ['en', 'ar'].includes(req.query.lang)) {
        req.session.lang = req.query.lang;
    }
    const current_lang = req.session.lang || 'ar';
    const lang = current_lang === 'en' ? require('./includes/lang_en.js') : require('./includes/lang_ar.js');
    const dir = current_lang === 'en' ? 'ltr' : 'rtl';
    
    let settings = {};
    try {
        const [rows] = await pool.query("SELECT setting_key, setting_value FROM settings");
        rows.forEach(r => settings[r.setting_key] = r.setting_value);
    } catch(e) {}
    
    const site_name = settings['site_name_' + current_lang] || (current_lang === 'ar' ? 'راحة سبا' : 'Raha Spa');
    
    res.locals.current_lang = current_lang;
    res.locals.lang = lang;
    res.locals.dir = dir;
    res.locals.settings = settings;
    res.locals.site_name = site_name;
    res.locals._SESSION = req.session; // for ejs template access to $_SESSION equivalent
    
    // PHP Polyfills
    res.locals.number_format = (num, dec) => Number(num).toFixed(dec);
    res.locals.strtotime = (str) => new Date(str).getTime();
    res.locals.date = (fmt, ts) => new Date(ts).toLocaleDateString();
    res.locals.empty = (val) => !val || val.length === 0;
    
    next();
});

// Import Routes
app.use('/', require('./routes/pages'));
app.use('/', require('./routes/api'));
app.use('/admin', require('./routes/admin'));

// Remove .php extension dynamically or redirect
app.get('/:page.php', (req, res, next) => {
    // We already handle specific routes in pages.js, but if anything falls through:
    next();
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
});
