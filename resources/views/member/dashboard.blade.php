<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Member · KazuhaDev.</title><link rel="icon" href="{{ asset('kz-brand.webp') }}" type="image/webp"><x-theme-head/>@vite(['resources/css/app.css','resources/js/app.js'])<style>
.member-studio{font-family:'Manrope','Plus Jakarta Sans','Segoe UI Variable','Segoe UI',system-ui,sans-serif}
.member-studio .member-app-shell .member-main{max-width:1440px;padding:28px clamp(16px,3vw,40px) 24px}
.member-studio .ms-content{display:grid;gap:20px;min-width:0}
.member-studio .ms-welcome{position:relative;isolation:isolate;overflow:hidden;padding:clamp(22px,3vw,36px);margin:0;min-height:150px;border:1px solid var(--line);border-radius:22px;background:radial-gradient(ellipse at 95% 0%,#a76ecb38,transparent 65%),var(--panel);align-items:center}
.ms-welcome:after{content:"";position:absolute;right:-65px;top:-130px;width:330px;height:330px;border:1px solid #b88ed238;border-radius:50%;box-shadow:0 0 0 30px #b88ed20b,0 0 0 60px #b88ed208;z-index:-1;pointer-events:none}
.member-studio .ms-welcome h1{font-family:inherit;font-size:clamp(28px,4vw,46px);font-weight:750;letter-spacing:-.045em;line-height:1.15;margin:10px 0;overflow-wrap:anywhere}
.member-studio .ms-welcome p{font-size:14px;line-height:1.65;margin:0;color:var(--muted)}
.member-studio .ms-welcome .badge{flex:none;padding:9px 12px;border-radius:9px;font-weight:600}
.ms-overview-grid{display:grid;grid-template-columns:minmax(0,.85fr) minmax(0,1.25fr);gap:18px;align-items:stretch}
.member-studio .ms-overview-grid .member-key,.member-studio .ms-overview-grid .duration-panel{margin:0!important;min-width:0;border-radius:18px;border:1px solid var(--line)}
.member-studio .ms-overview-grid .member-key{display:flex;flex-direction:column;align-items:stretch;justify-content:space-between;gap:16px;padding:22px;background:var(--panel)}
.ms-kicker{display:flex;align-items:center;gap:7px;color:var(--mint);font-size:10px;letter-spacing:1.1px;font-weight:700;margin-bottom:10px}
.member-studio .member-key h2,.member-studio .duration-panel h2{font-size:21px;letter-spacing:-.025em;margin:0 0 10px}
.member-studio .member-key code{display:block;white-space:normal;overflow-wrap:anywhere;padding:13px;background:var(--soft);border:1px solid var(--line);border-radius:9px;font-size:13px;line-height:1.7}
.member-studio .member-key p{font-size:12px;line-height:1.6;margin:12px 0 0}
.member-studio .member-key .btn{min-height:42px;justify-content:center}
.member-studio .duration-panel{overflow:hidden;background:radial-gradient(ellipse at 90% 0%,#b380ca35,transparent 70%),var(--panel)}
.member-studio .duration-panel .panel-heading{padding:22px 22px 10px}
.member-studio .member-countdown{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;padding:12px 22px 20px;margin:0}
.member-studio .member-countdown>div{display:grid;justify-items:center;gap:6px;padding:13px 3px;border:1px solid var(--line);border-radius:12px;background:var(--soft);min-width:0}
.member-studio .member-countdown strong{font-size:clamp(24px,3vw,44px);line-height:1.05;font-weight:750;letter-spacing:-.05em;font-variant-numeric:tabular-nums}
.member-studio .member-countdown span{font-size:10px;letter-spacing:1px}
.member-studio .duration-meta{display:flex;flex-wrap:wrap;gap:8px;justify-content:space-between;padding:12px 22px;border-top:1px solid var(--line);font-size:11px}
.ms-section-label{display:grid;gap:6px;margin-top:4px}.ms-section-label>span{font-size:10px;letter-spacing:1.3px;color:var(--mint);font-weight:700}.ms-section-label h2{font-size:23px;letter-spacing:-.03em;margin:0}
.member-studio .member-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin:0;align-items:stretch}
.member-studio .member-grid>.panel{border-radius:18px;overflow:hidden;display:flex;flex-direction:column;min-width:0;transition:border-color .2s,box-shadow .2s}
.member-studio .member-grid>.panel:hover{border-color:var(--mint);box-shadow:0 8px 24px #a170b515}
.member-studio .member-grid .panel-heading{padding:20px 22px;border-bottom:1px solid var(--line);background:var(--soft)}
.member-studio .member-grid .panel-heading h2{font-size:19px;letter-spacing:-.02em;margin:0 0 5px}
.member-studio .member-grid .form-content{padding:20px 22px;display:flex;flex-direction:column;gap:12px;flex:1}
.member-studio .member-grid .form-content p{margin:0;line-height:1.65}
.member-studio .member-grid .hwid-value{border-radius:10px;padding:12px}
.member-studio .member-grid .member-facts{margin:0}
.member-studio .member-grid .member-facts>div{padding:11px 0;gap:10px;border-bottom:1px solid var(--line)}
.member-studio .member-grid .btn{min-height:44px;border-radius:10px;font-weight:650}
.member-studio .renewal-steps{display:grid;gap:7px;margin:0}
.member-studio .renewal-steps>span{padding:9px;border:1px solid var(--line);border-radius:8px;background:var(--soft)}
.member-studio .ms-library{margin:0}.member-studio .ms-library .page-heading{margin:8px 0 16px}
.member-studio .ms-library .game-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,300px),1fr));gap:16px;justify-content:start}
.member-studio .ms-library .game-card{border-radius:18px;overflow:hidden;transition:transform .2s,border-color .2s}
.member-studio .ms-library .game-card:hover{transform:translateY(-3px);border-color:var(--mint)}
.member-studio .ms-library .game-card-body{padding:18px;gap:10px}
.member-studio .ms-footer{margin-top:24px;padding:16px 20px;border:1px solid #574461;border-radius:13px;background:linear-gradient(115deg,#201b29,#35283e);color:#e3d3ec;font-size:12px}
.member-studio .ms-content>.ms-welcome,.member-studio .ms-content>.ms-overview-grid,.member-studio .ms-content>.member-grid,.member-studio .ms-content>.ms-library{animation:ms-enter .5s ease both}
.member-studio .ms-content>.ms-overview-grid{animation-delay:.06s}.member-studio .ms-content>.member-grid{animation-delay:.12s}.member-studio .ms-content>.ms-library{animation-delay:.18s}
@keyframes ms-enter{from{opacity:0;transform:translateY(9px)}to{opacity:1;transform:translateY(0)}}
:root[data-theme="light"] .member-studio .ms-welcome,:root[data-theme="light"] .member-studio .duration-panel{--panel:#292233;--soft:#3c3047;--line:#65536f;--text:#fff;--muted:#e0d3e7;--mint:#f2b3df;color:var(--text)}
:root[data-theme="light"] .member-studio .ms-welcome h1,:root[data-theme="light"] .member-studio .duration-panel h2{color:#fff}
:root[data-theme="light"] .member-studio .ms-overview-grid .member-key,:root[data-theme="light"] .member-studio .member-grid>.panel{background:#fff;border-color:#cbbfd3;box-shadow:0 6px 18px #34213e0d}
@media(max-width:1050px){.ms-overview-grid{grid-template-columns:minmax(0,1fr)}}
@media(max-width:700px){.member-studio .member-grid{grid-template-columns:minmax(0,1fr)}.member-studio .ms-content{gap:14px}.member-studio .ms-welcome{flex-wrap:wrap;gap:12px}.member-studio .member-countdown{padding:10px 14px 16px;gap:5px}.member-studio .member-countdown>div{padding:10px 2px}.member-studio .duration-panel .panel-heading,.member-studio .duration-meta{padding-inline:14px}}
@media(prefers-reduced-motion:reduce){.member-studio .ms-content>*{animation:none!important}.member-studio .ms-library .game-card:hover{transform:none}}

/* Consistent charcoal dashboard cards on the light canvas. */
:root[data-theme="light"] .member-studio .ms-content :is(.member-key,.duration-panel,.member-device,.renewal-panel,.ms-library .game-card){--panel:#292233;--soft:#382e43;--field:#211b2b;--line:#594963;--text:#f9f3fc;--muted:#d4c5de;--mint:#efb3df;--mint-bg:#493349;--red:#ffb5c4;--accent-purple:#cdb3f3;color:var(--text);background:radial-gradient(ellipse at 100% 0%,#a77ac315,transparent 65%),var(--panel);border-color:var(--line);box-shadow:0 7px 20px #30213d16}
:root[data-theme="light"] .member-studio .ms-content :is(.member-key,.duration-panel,.member-device,.renewal-panel,.ms-library .game-card) :is(h2,h3,strong,dd){color:var(--text)}
:root[data-theme="light"] .member-studio .ms-content :is(.member-key,.duration-panel,.member-device,.renewal-panel,.ms-library .game-card) :is(p,dt,small,.field-help,.game-card-description){color:var(--muted)}
:root[data-theme="light"] .member-studio .member-grid .panel-heading{background:var(--soft);border-color:var(--line)}
:root[data-theme="light"] .member-studio :is(.member-key code,.hwid-value,.renewal-steps>span,.member-countdown>div){background:var(--soft);border-color:var(--line);color:var(--mint)}
:root[data-theme="light"] .member-studio .member-countdown strong{color:var(--mint)}
:root[data-theme="light"] .member-studio .member-facts>div{border-color:var(--line)}
:root[data-theme="light"] .member-studio .ms-content :is(.member-key,.member-device,.renewal-panel,.ms-library .game-card) .btn.secondary{background:var(--soft);color:var(--text);border-color:var(--line)}
:root[data-theme="light"] .member-studio .ms-content :is(.renewal-panel,.ms-library .game-card) .btn.primary{background:linear-gradient(110deg,#efb3df,#c5a8ef);color:#30203a;border-color:transparent}
:root[data-theme="light"] .member-studio .ms-content .btn:disabled{opacity:.55;cursor:not-allowed}
:root[data-theme="light"] .member-studio .ms-library .empty-state{background:#292233;border:1px solid #594963;border-radius:18px;color:#f9f3fc;padding:26px;--muted:#d4c5de;--mint:#efb3df}
:root[data-theme="light"] .member-studio .ms-library .empty-state p{color:#d4c5de}

/* Full-width library empty state and readable typography in both themes. */
.member-studio .ms-library .page-heading h2{font-size:23px;font-weight:700;letter-spacing:-.03em;margin:6px 0}
.member-studio .ms-library .game-grid:has(>.ms-library-empty){grid-template-columns:minmax(0,1fr);max-width:none;width:100%;margin:0;padding:0;justify-items:stretch}
.member-studio .ms-library .ms-library-empty{grid-column:1/-1;width:100%;max-width:none;min-height:150px;box-sizing:border-box;display:flex;flex-direction:row;align-items:center;justify-content:space-between;gap:20px;margin:0;padding:24px;border-radius:18px;text-align:left;border:1px solid var(--line);background:radial-gradient(ellipse at 100% 0%,#a77ac315,transparent 65%),var(--panel)}
.ms-library-symbol{display:grid;place-items:center;flex:none;width:68px;height:68px;border:1px solid var(--line);border-radius:16px;background:var(--soft);color:var(--mint)}
.ms-library-empty-copy{flex:1;min-width:0;display:grid;gap:7px}
.ms-library-caption{font-size:10px;font-weight:700;letter-spacing:1.2px;color:var(--mint)}
.member-studio .ms-library .ms-library-empty strong{font-size:20px;font-weight:700;line-height:1.3;color:var(--text);margin:0}
.member-studio .ms-library .ms-library-empty p{font-size:13px;color:var(--muted);line-height:1.6;max-width:600px;margin:0}
.ms-library-empty-status{display:flex;align-items:center;gap:7px;flex:none;padding:8px 11px;border:1px solid var(--line);border-radius:8px;background:var(--soft);color:var(--muted);font-size:11px}
:root[data-theme="light"] .member-studio .ms-library .ms-library-empty{--panel:#292233;--soft:#382e43;--line:#594963;--text:#f9f3fc;--muted:#d4c5de;--mint:#efb3df;background:radial-gradient(ellipse at 100% 0%,#a77ac315,transparent 65%),var(--panel);border-color:var(--line);box-shadow:0 7px 20px #30213d16}
:root[data-theme="light"] .member-studio .ms-library .ms-library-empty strong{color:#f9f3fc}
@media(max-width:650px){.member-studio .ms-library .ms-library-empty{flex-wrap:wrap;gap:12px;padding:18px}.ms-library-symbol{width:48px;height:48px}.ms-library-empty-copy{flex:1 1 180px}.ms-library-empty-status{margin-left:60px}.member-studio .ms-library .ms-library-empty strong{font-size:17px}}
</style><style>
body.member-with-sidebar{font-family:'Manrope','Plus Jakarta Sans','Segoe UI Variable','Segoe UI',system-ui,sans-serif}
.member-with-sidebar .member-app-shell .member-header{background:var(--panel);border-bottom:1px solid var(--line);box-shadow:0 3px 18px #0000000b}
.member-with-sidebar .member-header .breadcrumbs{font-weight:500;min-width:0}
.member-with-sidebar .member-header .breadcrumbs strong{font-weight:700}
.member-with-sidebar .member-header-actions .btn{min-height:36px;border-radius:9px}
.member-with-sidebar .member-header-actions>a.btn{background:var(--mint-bg);color:var(--mint);border-color:var(--line)}
.member-with-sidebar .member-header-actions>a.btn:hover{border-color:var(--mint)}
.member-with-sidebar .member-main .ms-footer{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-top:24px;padding:16px 20px;border:1px solid #574461;border-radius:13px;background:linear-gradient(115deg,#201b29,#35283e);color:#e3d3ec;font-size:12px}
:root[data-theme="light"] .member-with-sidebar .member-app-shell .member-header{background:#fff;border-color:#cbbfd3}
@media(max-width:650px){.member-with-sidebar .member-app-shell .member-header{height:auto;min-height:68px;padding-block:10px;gap:7px;flex-wrap:wrap}.member-with-sidebar .member-header-actions{gap:5px}.member-with-sidebar .member-main .ms-footer{padding:12px}}
</style><style>
.member-with-sidebar .member-app-shell .member-header.ms-topbar-v6{background:linear-gradient(110deg,#211a2b,#30243c);border-bottom:1px solid #51415d;color:#f8effb;--panel:#251d30;--soft:#382c45;--line:#574663;--text:#f8effb;--muted:#d0c0da;--mint:#efb3df;--mint-bg:#4c334d;min-height:76px;height:auto;padding-block:12px;box-shadow:0 5px 20px #0002}
.ms-topbar-v6 .sidebar-toggle{background:#34283f;border:1px solid #51415d;color:#eed9f4;border-radius:10px;width:38px;height:38px}
.ms-topbar-v6 .breadcrumbs{display:flex;align-items:center;gap:9px;color:#cdbed7}
.ms-topbar-v6 .breadcrumbs strong{color:#fff;font-size:14px}
.ms-topbar-label{display:flex;flex-direction:column;gap:3px;min-width:0}
.ms-topbar-label small{font-size:9px;letter-spacing:1.2px;font-weight:650;color:#d2b2dd}
.member-with-sidebar .ms-topbar-v6 .member-header-actions .btn{min-height:38px;border-radius:9px}
.member-with-sidebar .ms-topbar-v6 .member-header-actions>a.btn{background:linear-gradient(110deg,#efb3df,#c5a8ef);color:#30203a;border:0;font-weight:700;padding-inline:14px}
.member-with-sidebar .ms-topbar-v6 .member-header-actions .btn.secondary{background:#382c45;border-color:#594764;color:#f8effb}
.member-with-sidebar .ms-topbar-v6 .member-header-actions>a.btn.secondary{background:linear-gradient(110deg,#efb3df,#c5a8ef);color:#30203a;border:0}
.ms-topbar-v6 .member-header-actions .notification-panel{background:var(--panel);color:var(--text)}
.member-with-sidebar .member-main .ms-footer-v6{display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap;padding:19px 22px;margin-top:24px;border:1px solid #594763;border-radius:16px;background:linear-gradient(115deg,#241b30,#35283e);color:#f4eaf8}
.ms-footer-v6 .ms-footer-brand,.ms-footer-v6 .ms-footer-status{display:flex;align-items:center;gap:10px}
.ms-footer-brand img{width:36px;height:36px;object-fit:cover;border-radius:9px}
.ms-footer-brand strong,.ms-footer-brand small,.ms-footer-status strong,.ms-footer-status small{display:block;line-height:1.6}
.ms-footer-brand strong,.ms-footer-status strong{font-size:12px;color:#fff}
.ms-footer-brand small,.ms-footer-status small{font-size:11px;color:#d5c5df}
.ms-footer-v6 .ms-footer-links{display:flex;gap:14px;flex-wrap:wrap}
.ms-footer-links a{display:inline-flex;align-items:center;gap:5px;color:#efd5ec;font-size:11px;text-decoration:none}
.ms-footer-links a:hover{color:#fff}
.ms-footer-v6 .status-dot{background:#9de1c5;box-shadow:0 0 0 3px #9de1c520}
@media(max-width:650px){.member-with-sidebar .member-app-shell .member-header.ms-topbar-v6{padding:10px 12px;gap:8px;min-height:64px;flex-wrap:wrap}.ms-topbar-v6 .breadcrumbs{max-width:none!important;flex:1;gap:5px}.ms-topbar-v6 .breadcrumbs strong{font-size:12px}.ms-topbar-v6 .member-header-actions{flex-wrap:wrap;justify-content:flex-end}.member-with-sidebar .member-main .ms-footer-v6{padding:15px;gap:12px}.ms-footer-v6 .ms-footer-links{width:100%;padding-top:10px;border-top:1px solid #594763}}
</style><style>
/* Override the older light topbar rule with matching specificity. */
:root[data-theme="light"] .member-with-sidebar .member-app-shell .member-header.ms-topbar-v6{background:linear-gradient(110deg,#211a2b,#30243c);border-color:#51415d;color:#f8effb}
:root[data-theme="light"] .member-with-sidebar .ms-topbar-v6 .breadcrumbs strong{color:#fff}
:root[data-theme="light"] .member-with-sidebar .ms-topbar-v6 .ms-topbar-label small{color:#d2b2dd}
</style><style>
.member-studio .ms-renew-dialog{--panel:#292233;--soft:#382e43;--line:#594963;--text:#f9f3fc;--muted:#d4c5de;--mint:#efb3df;--mint-bg:#503449;background:var(--panel);color:var(--text);border:1px solid var(--line);border-radius:20px;padding:24px;width:min(540px,calc(100vw - 28px));max-height:calc(100dvh - 32px);overflow:auto;box-shadow:0 24px 80px #0007}
.ms-renew-dialog::backdrop{background:#100c17a8;backdrop-filter:blur(7px)}
.ms-renew-dialog[open]{animation:ms-renew-enter .25s ease both}
.ms-renew-heading{display:flex;align-items:center;gap:12px}.ms-renew-heading>div{flex:1}.ms-renew-heading h2{color:var(--text);font-size:24px;letter-spacing:-.035em;margin:3px 0}.ms-renew-eyebrow{color:var(--mint);font-size:10px;letter-spacing:1.2px;font-weight:700}.ms-renew-icon,.ms-renew-option-icon{display:grid;place-items:center;background:var(--soft);border:1px solid var(--line);color:var(--mint);border-radius:12px;flex:none;width:48px;height:48px}.ms-renew-description{color:var(--muted);font-size:13px;line-height:1.6;margin:14px 0}.ms-renew-identity{display:flex;align-items:center;gap:10px;background:var(--soft);border:1px solid var(--line);padding:12px;border-radius:10px;color:var(--mint)}.ms-renew-identity small,.ms-renew-identity strong{display:block}.ms-renew-identity small{font-size:11px;color:var(--muted)}.ms-renew-identity strong{font-size:13px;margin-top:3px;color:var(--mint)}
.ms-renew-options{display:grid;gap:9px;border:0;padding:0;margin:18px 0;min-width:0}.ms-renew-options legend{font-weight:650;color:var(--text);font-size:13px;margin-bottom:10px}.ms-renew-dialog .ms-renew-option{display:flex;position:relative;align-items:center;gap:10px;padding:12px;margin:0;border:1px solid var(--line);border-radius:12px;background:var(--soft);cursor:pointer;transition:background .18s,border-color .18s}.ms-renew-option input{position:absolute;width:1px;height:1px;opacity:0;padding:0;min-height:0}.ms-renew-option:has(input:checked){border-color:var(--mint);background:var(--mint-bg);box-shadow:0 0 0 1px var(--mint)}.ms-renew-option:has(input:focus-visible){outline:2px solid var(--mint);outline-offset:3px}.ms-renew-option-icon{width:38px;height:38px}.ms-renew-option-copy{flex:1;min-width:0}.ms-renew-option-copy strong,.ms-renew-option-copy small{display:block}.ms-renew-option-copy strong{font-size:15px;color:var(--text)}.ms-renew-option-copy small{font-size:11px;color:var(--muted);margin-top:3px}.ms-renew-price{font-size:14px;color:var(--text);white-space:nowrap}.ms-renew-radio{width:21px;height:21px;border:1px solid var(--muted);border-radius:50%;display:grid;place-items:center;color:transparent;flex:none}.ms-renew-option:has(input:checked) .ms-renew-radio{background:var(--mint);color:#30203a;border-color:var(--mint)}.ms-renew-note,.ms-renew-empty{display:flex;align-items:center;gap:9px;font-size:12px;line-height:1.6;color:var(--muted);background:var(--soft);padding:11px;border:1px solid var(--line);border-radius:10px}.ms-renew-note svg,.ms-renew-empty svg{flex:none;color:var(--mint)}.ms-renew-actions{display:flex;gap:9px;justify-content:flex-end;margin-top:18px}.ms-renew-dialog .btn.secondary{background:var(--soft);color:var(--text);border-color:var(--line)}.ms-renew-dialog .btn.primary,.member-studio .ms-renew-trigger{background:linear-gradient(110deg,#efb3df,#c5a8ef);color:#30203a;border:0}.ms-renew-dialog form:not(:has(input[type=radio]:checked)) .ms-renew-submit{opacity:.5}.ms-renew-pending{display:grid;gap:8px;margin-top:14px;color:var(--mint);font-size:12px}.ms-renew-pending a{display:flex;gap:8px;align-items:center;padding:10px;background:var(--soft);border:1px solid var(--line);border-radius:8px;color:var(--text)}.ms-renew-pending a>span:first-child{flex:1}.ms-renew-pending small{display:block;color:var(--muted)}
@keyframes ms-renew-enter{from{opacity:0;transform:translateY(10px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}@media(max-width:450px){.member-studio .ms-renew-dialog{padding:16px}.ms-renew-dialog .ms-renew-option{gap:7px;padding:10px}.ms-renew-option-icon{display:none}.ms-renew-price{font-size:12px}.ms-renew-actions .btn{flex:1}}@media(prefers-reduced-motion:reduce){.ms-renew-dialog[open]{animation:none}}
</style><style>
.member-studio .ms-renew-dialog{width:min(700px,calc(100vw - 28px))}
.ms-renew-games{margin-top:14px;padding:13px;border:1px solid var(--line);border-radius:12px;background:var(--soft)}
.ms-renew-section-head{display:flex;align-items:center;justify-content:space-between;gap:8px;color:var(--text)}
.ms-renew-section-head strong{display:flex;align-items:center;gap:7px;font-size:13px;color:var(--text)}
.ms-renew-section-head small{font-size:11px;color:var(--mint)}
.ms-renew-game-list{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px}
.ms-renew-game-list>span{display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border:1px solid var(--line);border-radius:7px;background:var(--panel);color:var(--text);font-size:11px}
.ms-renew-game-list svg{color:var(--mint)}
.ms-renew-games p{font-size:11px;line-height:1.6;color:var(--muted);margin:9px 0 0}
.ms-renew-tools{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,160px);gap:8px}
.ms-renew-search{display:flex;align-items:center;gap:8px;padding:0 10px;border:1px solid var(--line);border-radius:9px;background:#211b2b;color:var(--mint)}
.ms-renew-dialog .ms-renew-search input{min-width:0;width:100%;padding:10px 0;background:transparent;color:var(--text);border:0;box-shadow:none;font-size:12px}
.ms-renew-dialog .ms-renew-search input::placeholder{color:var(--muted)}
.ms-renew-dialog .ms-renew-tools select{width:100%;min-width:0;border:1px solid var(--line);border-radius:9px;background:#211b2b;color:var(--text);font-size:12px}
.ms-renew-carousel-nav{display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:11px;color:var(--muted)}
.ms-renew-carousel-nav>div{display:flex;gap:6px}
.ms-renew-carousel-nav button{min-width:32px;min-height:30px}
.ms-renew-track{display:flex;gap:10px;overflow-x:auto;scroll-snap-type:x mandatory;scrollbar-width:thin;scrollbar-color:#9873a7 #211b2b;padding:3px 2px 10px}
.ms-renew-dialog .ms-renew-track .ms-renew-option{flex:0 0 calc((100% - 10px)/2);min-width:0;display:grid;grid-template-columns:38px minmax(0,1fr) 21px;align-content:start;gap:10px;scroll-snap-align:start}
.ms-renew-track .ms-renew-option-copy{grid-column:2;grid-row:1}
.ms-renew-track .ms-renew-radio{grid-column:3;grid-row:1;align-self:center}
.ms-renew-track .ms-renew-price{grid-column:1/-1;padding-top:10px;border-top:1px solid var(--line);font-size:19px}
.ms-renew-package-description{overflow-wrap:anywhere;line-height:1.45}
.ms-renew-dialog [hidden]{display:none!important}
.ms-renew-dialog .ms-renew-track .ms-renew-option:only-child{flex-basis:100%}
@media(max-width:560px){.ms-renew-dialog .ms-renew-track .ms-renew-option{flex-basis:85%;grid-template-columns:minmax(0,1fr) 21px}.ms-renew-track .ms-renew-option-icon{display:none}.ms-renew-track .ms-renew-option-copy{grid-column:1}.ms-renew-track .ms-renew-radio{grid-column:2}.ms-renew-tools{grid-template-columns:1fr}}
</style><style>
.ms-renew-dialog .ms-renew-track .ms-renew-option{padding:16px;gap:12px;align-items:start}
.ms-renew-dialog .ms-renew-option-copy>strong{font-size:18px;line-height:1.3;overflow-wrap:anywhere}
.ms-renew-dialog .ms-renew-option-copy>small{line-height:1.6;white-space:normal;overflow-wrap:anywhere}
.ms-renew-dialog .ms-renew-option-copy .ms-pkg-label{color:var(--mint);font-size:9px;letter-spacing:1px;font-weight:700;margin:0 0 4px}
.ms-pkg-facts{grid-column:1/-1;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;padding:11px;background:#211b2b;border:1px solid var(--line);border-radius:10px}
.ms-pkg-facts>span{display:flex;gap:7px;align-items:flex-start;min-width:0}
.ms-pkg-facts svg{color:var(--mint);flex:none;margin-top:2px}
.ms-pkg-facts small,.ms-pkg-facts strong{display:block;font-size:11px;line-height:1.5;overflow-wrap:anywhere}
.ms-pkg-facts small{color:var(--muted);font-size:10px}.ms-pkg-facts strong{color:var(--text);font-weight:650}
.ms-pkg-access{grid-column:1/-1;display:grid;gap:6px}
.ms-pkg-access>small{font-size:10px;color:var(--muted);line-height:1.5}
.ms-pkg-games{display:flex;flex-wrap:wrap;gap:5px}
.ms-pkg-games>span{display:inline-flex;align-items:center;gap:4px;border:1px solid var(--line);background:var(--panel);color:var(--text);border-radius:6px;padding:5px 7px;font-size:11px}
.ms-pkg-games svg{color:var(--mint)}
.ms-renew-dialog .ms-renew-track .ms-renew-price{display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap}
.ms-renew-price small{font-size:11px;color:var(--muted);font-weight:400}
.ms-renew-price strong{font-size:20px;color:var(--text)}
@media(max-width:380px){.ms-pkg-facts{grid-template-columns:1fr}}
</style><style>
.ms-pkg-artworks{display:grid;gap:7px}.ms-pkg-artworks:empty{display:none}
.ms-pkg-artwork{display:flex;align-items:flex-end;position:relative;isolation:isolate;overflow:hidden;min-height:78px;border:1px solid var(--line);border-radius:9px;background:#211b2b}
.ms-pkg-banner{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:-2}
.ms-pkg-artwork:before{content:"";position:absolute;inset:0;background:linear-gradient(0deg,#17111de8,#17111d18);z-index:-1}
.ms-pkg-artwork-caption{display:flex;align-items:center;gap:8px;padding:9px;color:#fff;width:100%;min-width:0}
.ms-pkg-artwork-caption strong{font-size:12px;color:#fff;overflow-wrap:anywhere}
.ms-pkg-logo{width:28px;height:28px;object-fit:contain;flex:none}
</style><style>
/* Compact renewal selector: aligned controls and persistent checkout actions. */
.member-studio .ms-renew-dialog{padding:20px 24px 0;max-height:calc(100dvh - 32px);scroll-padding-block:20px 100px}
.ms-renew-dialog .ms-renew-tools{align-items:stretch;grid-template-columns:minmax(0,1fr) minmax(0,160px);gap:9px}
.ms-renew-dialog .ms-renew-tools>label,.ms-renew-dialog .ms-renew-tools>select{margin:0!important;align-self:stretch;height:42px;min-height:42px;box-sizing:border-box}
.ms-renew-dialog .ms-renew-search input{margin:0!important;height:40px;min-height:0;padding:0}
.ms-renew-dialog .ms-renew-options{gap:8px;margin:14px 0}
.ms-renew-dialog .ms-renew-carousel-nav{min-height:30px}
.ms-renew-dialog .ms-renew-carousel-nav button{height:30px;min-height:30px;padding:0 10px}
.ms-renew-dialog .ms-renew-track .ms-renew-option{gap:9px;padding:13px}
.ms-renew-dialog .ms-pkg-facts{padding:9px;gap:7px 12px}
.ms-renew-dialog .ms-pkg-access{gap:5px}
.ms-renew-dialog .ms-pkg-artwork{min-height:82px}
.ms-renew-dialog .ms-pkg-banner{object-position:center}
.ms-renew-dialog .ms-renew-actions{position:sticky;bottom:0;z-index:5;background:var(--panel);border-top:1px solid var(--line);margin:14px -24px 0;padding:13px 24px;box-shadow:0 -8px 20px #17111d38}
.ms-renew-dialog .ms-renew-track .ms-renew-price{padding-top:9px}
.ms-renew-dialog .ms-renew-identity,.ms-renew-dialog .ms-renew-games{padding:10px}
.ms-renew-dialog .ms-renew-games{margin-top:10px}
.ms-renew-dialog .ms-renew-description{margin:10px 0}
@media(max-width:560px){.member-studio .ms-renew-dialog{padding:16px 16px 0}.ms-renew-dialog .ms-renew-tools{grid-template-columns:minmax(0,1fr) minmax(0,128px)}.ms-renew-dialog .ms-renew-actions{margin-inline:-16px;padding:12px 16px}.ms-renew-dialog .ms-renew-track .ms-renew-option{flex-basis:90%}}
@media(max-width:360px){.ms-renew-dialog .ms-renew-tools{grid-template-columns:1fr}}
</style><style>
.member-studio .ms-renew-await{position:relative;border-color:#b78b50;background:linear-gradient(145deg,#34282f,#231d2b 50%,#211b2a);overflow:hidden;color:#faf0e8}
.ms-renew-await:before{content:"";position:absolute;inset:0 0 auto;height:2px;background:linear-gradient(90deg,transparent,#efbf7c,#f0a9dc,transparent);background-size:200% 100%;animation:ms-await-sweep 3s linear infinite;pointer-events:none}
.member-studio .ms-renew-await .panel-heading{background:#352a36;border-color:#73583f;color:#faf0e8}
.ms-renew-await .panel-heading h2{color:#fff6ef!important}.ms-renew-await .panel-heading p{color:#e1c6b0!important}
.ms-await-kicker{display:flex;align-items:center;gap:7px;color:#f1bf83;font-size:10px;font-weight:750;letter-spacing:1px;margin-bottom:5px}
.ms-await-kicker i,.ms-await-status i{display:inline-block;width:7px;height:7px;border-radius:50%;background:#f3bc72;box-shadow:0 0 0 0 #f3bc7260;animation:ms-await-pulse 2s infinite;flex:none}
.ms-await-orbit{display:grid;place-items:center;width:46px;height:46px;border-radius:13px;background:#49363a;color:#f3c38e;border:1px solid #8d684b;animation:ms-await-float 3s ease-in-out infinite}
.ms-await-notice{display:flex;gap:9px;align-items:center;background:#3b2e36;border:1px solid #795a43;padding:10px 12px;border-radius:10px;color:#efd3b8;font-size:12px;line-height:1.6}.ms-await-notice svg{flex:none;color:#f3bc72}
.ms-await-list{display:grid;gap:10px;max-height:360px;overflow:auto;scrollbar-width:thin}
.ms-await-invoice{display:grid;gap:10px;border:1px solid #775940;background:#2e2533;border-radius:12px;padding:13px}
.ms-await-top,.ms-await-details,.ms-await-meta{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
.ms-await-status{display:inline-flex;align-items:center;gap:7px;color:#f5c487;font-size:11px;font-weight:700}
.ms-await-top small,.ms-await-details small,.ms-await-meta{font-size:11px;color:#dcc7d7}.ms-await-details small{display:block;margin-top:3px;overflow-wrap:anywhere}.ms-await-details strong{font-size:14px;color:#fff1e7}
.ms-await-meta>span:first-child{display:flex;align-items:center;gap:5px;color:#f5d3a9}
.member-studio .ms-renew-await .ms-await-pay{background:linear-gradient(110deg,#f1c389,#e5afd8);border:0;color:#342536;font-weight:750;position:relative;overflow:hidden}
.ms-await-pay:after{content:"";position:absolute;inset:0;pointer-events:none;background:linear-gradient(110deg,transparent 25%,#ffffff30 50%,transparent 75%);transform:translateX(-120%);animation:ms-await-shine 4s ease infinite}
.ms-await-progress{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px}
.ms-await-progress span{display:flex;align-items:center;gap:5px;font-size:10px;color:#c2afc7;line-height:1.4}.ms-await-progress i{display:grid;place-items:center;width:21px;height:21px;border-radius:50%;background:#392f43;color:#ccb9d1;font-style:normal;flex:none}.ms-await-progress .is-done i{background:#38544b;color:#c1e9d7}.ms-await-progress .is-current{color:#f3c38e}.ms-await-progress .is-current i{background:#624735;color:#ffdcaa;animation:ms-await-pulse 2s infinite}
.member-studio .ms-renew-await .ms-await-other{background:#382e43;border-color:#65516f;color:#f4e7f5}
.member-studio .ms-renew-await .form-content>small{color:#cbb9d0}
@keyframes ms-await-sweep{to{background-position:200% 0}}@keyframes ms-await-pulse{0%,100%{box-shadow:0 0 0 0 #f3bc7240}50%{box-shadow:0 0 0 5px #f3bc7200}}@keyframes ms-await-float{50%{transform:translateY(-3px)}}@keyframes ms-await-shine{0%,65%{transform:translateX(-120%)}90%,100%{transform:translateX(120%)}}
@media(prefers-reduced-motion:reduce){.ms-renew-await:before,.ms-renew-await i,.ms-await-orbit,.ms-await-pay:after{animation:none!important}}
@media(max-width:420px){.ms-await-progress span{flex-direction:column;align-items:flex-start}}
</style></head>
<body class="member-body member-with-sidebar member-studio" data-sidebar-scope="member" data-feed-url="{{ route('notifications.member',absolute:false) }}">
<x-member-sidebar :license="$license"/><div class="app-shell member-app-shell"><header class="member-header ms-topbar-v6"><button class="icon-btn sidebar-toggle" type="button" id="menu-toggle" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false"><x-icon name="menu" size="20"/></button><div class="breadcrumbs"><span class="ms-topbar-label"><small>MEMBER WORKSPACE</small><strong>Dashboard saya</strong></span></div><div class="member-header-actions"><button type="button" class="btn primary compact ms-renew-trigger" data-open-dialog="member-renew-dialog"><x-icon name="clock" size="15"/> Perpanjang</button><x-notification-bell/><x-theme-toggle/><form method="post" action="{{ route('member.logout') }}" data-once data-member-logout>@csrf<button class="btn secondary compact" type="submit"><x-icon name="logout" size="15"/> Keluar</button></form></div></header>
<main class="member-main" data-member-status-url="{{ route('member.status',absolute:false) }}" data-member-login-url="{{ route('member.login',absolute:false) }}">
<div data-live-content class="ms-content" data-no-ui-icon><x-flash-messages/>
<div class="page-heading ms-welcome"><div><div class="eyebrow">MEMBER WORKSPACE / OVERVIEW</div><h1>Halo, {{ $license->member_name ?: $license->label }}.</h1><p>Kelola masa berlaku, perangkat, dan akses lisensi Anda dalam satu ruang.</p></div><span id="member-status" class="badge {{ $license->status() }}"><i></i>{{ ['active'=>'Lisensi aktif','unbound'=>'Menunggu perangkat','expired'=>'Kedaluwarsa','revoked'=>'Dicabut'][$license->status()] }}</span></div>
<div class="ms-overview-grid"><section class="panel member-key"><div><span class="ms-kicker"><x-icon name="key" size="16"/> IDENTITAS LISENSI</span><h2>License key Anda</h2><code>{{ $license->key_value ?? $license->key_hint }}</code><p>Simpan key ini dengan aman. Gunakan key yang sama setelah perpanjangan.</p></div>@if($license->key_value)<button class="btn secondary" data-copy="{{ $license->key_value }}"><x-icon name="copy" size="16"/> Salin key</button>@endif</section>

<section class="panel duration-panel"><div class="panel-heading"><div><span class="ms-kicker"><x-icon name="clock" size="15"/> MASA BERLAKU</span><h2>Durasi realtime</h2><p>{{ $license->expires_at ? 'Waktu terus berjalan sejak aktivasi pertama.' : 'Hitung mundur dimulai setelah aktivasi di injector.' }}</p></div><span class="api-symbol"><x-icon name="clock" size="24"/></span></div>
<div class="member-countdown" @if($license->expires_at) data-member-expires="{{ $license->expires_at->toIso8601String() }}" @endif>
@foreach(['Hari','Jam','Menit','Detik'] as $i=>$unit)<div><strong data-countdown-part="{{ $i }}">{{ !$license->expires_at ? str_pad((string) [intdiv($license->duration_seconds,86400),intdiv($license->duration_seconds % 86400,3600),intdiv($license->duration_seconds % 3600,60),$license->duration_seconds % 60][$i],2,'0',STR_PAD_LEFT) : '00' }}</strong><span>{{ $unit }}</span></div>@endforeach
</div><div class="duration-meta"><span id="member-timing-status"><span class="status-dot"></span> {{ !$license->activated_at ? 'Belum diaktifkan · durasi belum berjalan' : ($license->status()==='expired' ? 'Masa berlaku sudah habis' : 'Mengikuti waktu server') }}</span><span id="member-expiry-label">{{ $license->expires_at ? 'Expired: '.$license->expires_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s').' WIB' : '1 hari = 24 jam' }}</span></div></section></div>
<div class="ms-section-label"><span>02 / PENGELOLAAN LISENSI</span><h2>Perangkat & perpanjangan</h2></div><div class="member-grid"><section class="panel member-device"><div class="panel-heading"><div><h2>Perangkat terhubung</h2><p>Satu key terikat ke satu HWID.</p></div><x-icon name="device" size="22"/></div><div class="form-content"><label>HWID SAAT INI</label><div class="hwid-value"><code id="member-hwid">{{ $license->hwid ?? 'Belum ada perangkat terikat' }}</code>@if($license->hwid)<button class="icon-btn" id="member-hwid-copy" data-copy="{{ $license->hwid }}" aria-label="Salin HWID"><x-icon name="copy" size="17"/></button>@endif</div><dl class="member-facts"><div><dt>Aktivasi pertama</dt><dd id="member-activated">{{ $license->activated_at ? $license->activated_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s').' WIB' : 'Belum aktif' }}</dd></div><div><dt>Terakhir terhubung</dt><dd id="member-last-seen">{{ $license->last_seen_at?->timezone('Asia/Jakarta')->format('d M Y, H:i:s') ?? 'Belum ada' }}</dd></div><div><dt>Total reset</dt><dd id="member-reset-count">{{ $license->reset_count }} kali</dd></div></dl><form method="post" action="{{ route('member.reset') }}" data-confirm-title="Reset perangkat Anda?" data-confirm-action="Ya, reset HWID" data-confirm-icon="reset" data-confirm-hint="Reset tidak menambah atau mengulang durasi." data-confirm="Reset HWID perangkat Anda? Setelah reset, perangkat pertama yang melakukan validasi di injector akan terikat. Sisa durasi tidak berubah.">@csrf<button id="member-reset-button" type="submit" class="btn secondary full" @disabled($license->status()!=='active')><x-icon name="reset" size="16"/> Reset HWID saya</button></form><p class="field-help mb-0">Reset tidak menambah durasi. Tutup injector di perangkat lama, lalu buka kembali injector untuk mengikat perangkat. Maksimal satu reset per menit.</p></div></section>
@if($renewalInvoices->isNotEmpty())<section class="panel renewal-panel ms-renew-await" aria-labelledby="ms-await-title"><div class="panel-heading"><div><span class="ms-await-kicker"><i></i> PERPANJANGAN / MENUNGGU</span><h2 id="ms-await-title">Selesaikan perpanjangan</h2><p>{{ $renewalInvoices->count() }} invoice belum selesai.</p></div><span class="ms-await-orbit"><x-icon name="clock" size="24"/></span></div><div class="form-content"><div class="ms-await-notice"><x-icon name="info" size="18"/><span>Durasi key belum berubah. Lanjutkan invoice yang sudah dibuat dan tunggu konfirmasi pembayaran.</span></div><div class="ms-await-list">@foreach($renewalInvoices as $pending)<article class="ms-await-invoice"><div class="ms-await-top"><span class="ms-await-status"><i></i>{{ in_array($pending->gateway_status,['creating','uncertain']) ? 'Verifikasi gateway' : (in_array($pending->gateway_status,['failed','expired']) ? 'Perlu ditinjau' : 'Menunggu pembayaran') }}</span><small>{{ $pending->gateway_mode === 'sandbox' ? 'DEMO' : ($pending->gateway_mode === 'production' ? 'PRODUCTION' : 'INVOICE') }}</small></div><div class="ms-await-details"><div><strong>{{ $pending->product_name }}</strong><small>{{ $pending->number }}</small></div><strong>Rp {{ number_format($pending->amount,0,',','.') }}</strong></div><div class="ms-await-meta"><span><x-icon name="clock" size="14"/> +{{ \App\Support\LicenseDuration::label($pending->duration_seconds ?? $pending->days * 86400) }}</span><span>{{ $pending->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</span></div><a class="btn full ms-await-pay" href="{{ route('checkout.receipt',['order'=>$pending->number]) }}"><x-icon name="wallet" size="17"/> {{ in_array($pending->gateway_status,['creating','uncertain','failed','expired']) ? 'Lihat status invoice' : 'Lanjutkan pembayaran' }} <x-icon name="arrow" size="16"/></a></article>@endforeach</div><div class="ms-await-progress" aria-label="Tahap perpanjangan"><span class="is-done"><i>✓</i> Invoice dibuat</span><span class="is-current"><i>2</i> Pembayaran</span><span><i>3</i> Durasi bertambah</span></div><button type="button" class="btn secondary full ms-await-other" data-open-dialog="member-renew-dialog">Lihat paket durasi lainnya <x-icon name="arrow" size="15"/></button><small>Durasi bertambah hanya setelah pembayaran terverifikasi.</small></div></section>@else<section class="panel renewal-panel"><div class="panel-heading"><div><h2>Butuh waktu lebih?</h2><p>Pilih paket untuk key yang sedang Anda gunakan.</p></div><x-icon name="key" size="22"/></div><div class="form-content"><p>Pilih paket yang sesuai dengan akses key Anda, lanjutkan pembayaran, dan pantau statusnya. Durasi ditambahkan setelah pembayaran terverifikasi.</p><button type="button" class="btn primary full" data-open-dialog="member-renew-dialog"><x-icon name="clock" size="17"/> Pilih durasi perpanjangan <x-icon name="arrow" size="16"/></button><div class="renewal-steps"><span><b>01</b> Pilih paket perpanjangan</span><span><b>02</b> Pilih durasi & lakukan pembayaran</span><span><b>03</b> Key yang sama mendapat tambahan waktu</span></div>@if($renewalUrl)<a class="btn secondary full" href="{{ $renewalUrl }}" target="_blank" rel="noopener noreferrer"><x-icon name="message" size="17"/> Perpanjang via WhatsApp <x-icon name="arrow" size="16"/></a>@else<button class="btn secondary full" disabled>Nomor WhatsApp admin belum diatur</button>@endif<small>Pembayaran online dikonfirmasi gateway. Pembayaran manual diverifikasi administrator.</small></div></section>@endif</div>
@php($isDemoLicense = \App\Models\PurchaseOrder::where('issued_license_id', $license->id)->where('gateway_mode', 'sandbox')->exists())
<section class="member-games ms-library"><div class="page-heading"><div><div class="eyebrow">YOUR GAME LIBRARY</div><h2>Game & unduhan Anda</h2><p>Daftar game dan unduhan yang tersedia untuk lisensi Anda.</p></div><span class="subtle-chip">{{ $license->games->count() }} game</span></div><div class="game-grid">
@forelse($license->games as $game)<article class="panel game-card"><x-game-cover :game="$game" badge="AKSES KEY"/><div class="game-card-body"><div class="game-card-title"><div><span class="eyebrow">EXCLUSIVE ACCESS</span><h2>{{ $game->name }}</h2></div><span class="release-version">v{{ $game->release_version ?: '—' }}</span></div><p class="game-card-description">Akses terhubung ke lisensi dan perangkat Anda.</p>
<div class="download-facts"><span><x-icon name="device" size="15"/> Windows</span><span><x-icon name="download" size="15"/> {{ $game->injector_path ? number_format($game->injector_size/1048576,2).' MB' : 'Belum ada rilis' }}</span><span><x-icon name="shield" size="15"/> Key terverifikasi</span></div>
@if($isDemoLicense)<button class="btn secondary full" disabled><x-icon name="lock" size="16"/> Demo · unduhan produksi tidak tersedia</button>@elseif(!in_array($license->status(),['expired','revoked']) && $game->injector_path)<a class="btn primary full" href="{{ route('member.games.download',[$game,'injector']) }}"><x-icon name="download" size="18"/> Unduh Injector <x-icon name="arrow" size="16"/></a><details class="checksum"><summary>Verifikasi SHA-256 injector</summary><code>{{ $game->injector_sha256 }}</code></details>
@elseif(in_array($license->status(),['expired','revoked']))<button class="btn secondary full" disabled><x-icon name="lock" size="16"/> Perpanjang key untuk mengunduh</button>
@else<button class="btn secondary full" disabled>Injector sedang disiapkan</button>@endif
<div class="game-card-foot"><x-icon name="lock" size="12"/> Unduhan khusus pemilik key</div></div></article>@empty<div class="empty-state ms-library-empty"><span class="ms-library-symbol"><x-icon name="gamepad" size="32"/></span><div class="ms-library-empty-copy"><span class="ms-library-caption">GAME LIBRARY / 00</span><strong>Belum ada akses game</strong><p>Game yang terdaftar pada lisensi Anda akan muncul di sini. Hubungi admin jika akses belum tersedia.</p></div><span class="ms-library-empty-status"><x-icon name="info" size="15"/> Belum tersedia</span></div>@endforelse</div></section>
<dialog id="member-renew-dialog" class="modal ms-renew-dialog" data-no-ui-icon aria-labelledby="ms-renew-title"><div class="ms-renew-heading"><span class="ms-renew-icon"><x-icon name="clock" size="25"/></span><div><span class="ms-renew-eyebrow">MEMBER / RENEWAL</span><h2 id="ms-renew-title">Perpanjang lisensi</h2></div><button type="button" class="icon-btn" data-close-dialog aria-label="Tutup"><x-icon name="close" size="20"/></button></div><p class="ms-renew-description">Pilih tambahan durasi untuk key Anda. HWID dan akses game tetap sama.</p><div class="ms-renew-identity"><x-icon name="key" size="18"/><div><small>License key tujuan</small><strong>{{ $license->key_hint }}</strong></div></div>
@if($renewalInvoices->isNotEmpty())<div class="ms-renew-pending"><strong>Invoice perpanjangan belum selesai</strong>@foreach($renewalInvoices as $pending)<a href="{{ route('checkout.receipt', ['order' => $pending->number]) }}"><span>{{ $pending->product_name }}<small>{{ $pending->number }}</small></span><span>Rp {{ number_format($pending->amount,0,',','.') }}</span><x-icon name="arrow" size="15"/></a>@endforeach</div>@endif
<form method="post" action="{{ route('member.renew.store') }}" data-once>@csrf<input type="hidden" name="idempotency_key" value="{{ Illuminate\Support\Str::uuid() }}">
<section class="ms-renew-games"><div class="ms-renew-section-head"><strong><x-icon name="gamepad" size="17"/> Akses game saat ini</strong><small>{{ $license->games->count() }} game</small></div><div class="ms-renew-game-list">@forelse($license->games as $game)<span><x-icon name="check" size="13"/> {{ $game->name }}</span>@empty<span class="ms-renew-game-empty">Belum ada akses game terdaftar</span>@endforelse</div><p>Perpanjangan hanya menambah durasi, tidak menambah atau mengurangi akses game.</p></section>
<fieldset class="ms-renew-options"><legend>Pilih durasi tambahan</legend>@if($renewalProducts->isNotEmpty())<div class="ms-renew-tools"><label class="ms-renew-search"><x-icon name="search" size="16"/><input type="search" data-renew-search placeholder="Cari paket atau durasi" aria-label="Cari paket atau durasi"></label><select data-renew-filter aria-label="Filter durasi"><option value="all">Semua durasi</option><option value="short">Hingga 24 jam</option><option value="medium">2–7 hari</option><option value="long">Lebih dari 7 hari</option></select></div><div class="ms-renew-carousel-nav"><span data-renew-count aria-live="polite">{{ $renewalProducts->count() }} paket tersedia</span><div><button type="button" data-renew-scroll="-1" aria-label="Paket sebelumnya" class="btn secondary compact">←</button><button type="button" data-renew-scroll="1" aria-label="Paket berikutnya" class="btn secondary compact">→</button></div></div>@endif<div class="ms-renew-track" data-renew-track tabindex="0" aria-label="Daftar paket durasi">@forelse($renewalProducts as $product)<label class="ms-renew-option" data-renew-card data-duration="{{ $product->duration_seconds ?? $product->days * 86400 }}"><input type="radio" name="product_id" value="{{ $product->id }}" required><span class="ms-renew-option-icon"><x-icon name="clock" size="20"/></span><span class="ms-renew-option-copy"><small class="ms-pkg-label">PAKET PERPANJANGAN</small><strong>{{ $product->name }}</strong><small>{{ $product->description ?: 'Paket tambahan masa berlaku lisensi.' }}</small></span><span class="ms-pkg-facts"><span><x-icon name="clock" size="15"/><span><small>Tambahan durasi</small><strong>+{{ \App\Support\LicenseDuration::label($product->duration_seconds ?? $product->days * 86400) }}</strong></span></span><span><x-icon name="key" size="15"/><span><small>Jenis transaksi</small><strong>Perpanjangan key</strong></span></span><span><x-icon name="device" size="15"/><span><small>Perangkat</small><strong>HWID tetap</strong></span></span><span><x-icon name="shield" size="15"/><span><small>Aktivasi</small><strong>Setelah pembayaran terverifikasi</strong></span></span></span><span class="ms-pkg-access"><small>GAME DALAM KONFIGURASI PAKET</small><span class="ms-pkg-artworks">@foreach(collect($product->game_ids ?? [])->map(fn ($id) => $renewalGames->get($id))->filter() as $catalogGame)<span class="ms-pkg-artwork">@if($catalogGame->artworkUrl('banner'))<img class="ms-pkg-banner" src="{{ $catalogGame->artworkUrl('banner') }}" alt="" loading="lazy">@endif<span class="ms-pkg-artwork-caption">@if($catalogGame->logo_path && $catalogGame->artworkUrl('logo'))<img class="ms-pkg-logo" src="{{ $catalogGame->artworkUrl('logo') }}" alt="" loading="lazy">@else<x-icon name="gamepad" size="18"/>@endif<strong>{{ $catalogGame->name }}</strong></span></span>@endforeach</span>@if(collect($product->game_ids ?? [])->map(fn ($id) => $renewalGames->get($id))->filter()->isEmpty())<span class="ms-pkg-games"><span>Belum ada game pada paket ini</span></span>@endif<small>Akses game pada key tetap sama.</small></span><span class="ms-renew-price"><small>Total harga paket</small><strong>Rp {{ number_format($product->price,0,',','.') }}</strong></span><span class="ms-renew-radio" aria-hidden="true"><x-icon name="check" size="12"/></span></label>@empty<div class="ms-renew-empty"><x-icon name="info" size="22"/><span>Belum ada paket aktif. Hubungi admin.</span></div>@endforelse</div><p class="ms-renew-empty" data-renew-no-results hidden>Tidak ada paket yang cocok dengan filter.</p></fieldset>
<div class="ms-renew-note"><x-icon name="shield" size="17"/> Durasi ditambahkan setelah pembayaran terverifikasi. Harga mengikuti paket admin.</div><div class="ms-renew-actions"><button type="button" class="btn secondary" data-close-dialog>Batal</button><button type="submit" class="btn primary ms-renew-submit" @disabled($renewalProducts->isEmpty())>Lanjut ke pembayaran <x-icon name="arrow" size="16"/></button></div></form></dialog></div><footer class="footer ms-footer ms-footer-v6"><div class="ms-footer-brand"><img src="{{ asset('kz-brand.webp') }}" width="36" height="36" alt=""><span><strong>KazuhaDev.</strong><small>© {{ now()->year }} · Member portal</small></span></div><span class="ms-footer-status"><span class="status-dot"></span><span><strong>Waktu server</strong><small><time id="server-clock" data-time="{{ now()->toIso8601String() }}">{{ now()->timezone('Asia/Jakarta')->format('H:i:s') }}</time> WIB</small></span></span></footer>
</main></div><x-feedback/></body></html>
