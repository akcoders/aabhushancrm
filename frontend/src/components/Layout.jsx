import { useState } from "react";
import { NavLink, Outlet, useLocation } from "react-router-dom";
import {
    Bell,
    ChevronDown,
    LayoutDashboard,
    Users,
    UserRoundPlus,
    CalendarClock,
    ReceiptIndianRupee,
    Gem,
    CalendarDays,
    Tags,
    Gift,
    CreditCard,
    CheckSquare,
    BarChart3,
    Settings,
    UserCog,
    Menu,
    X,
    Sparkles,
    Megaphone,
    HeartHandshake,
    WandSparkles,
    SlidersHorizontal,
    Award,
    MessagesSquare,
    Video,
    Camera,
} from "lucide-react";
import { useAuth } from "../AuthContext";
const groups = [
    [
        "Overview",
        [
            ["/", "Dashboard", LayoutDashboard],
            ["/smart-work", "My Smart Work", WandSparkles],
        ],
    ],
    [
        "Relationships",
        [
            ["/leads", "Leads", UserRoundPlus],
            ["/followups", "Follow-ups", CalendarClock],
            ["/customers", "Customers", Users],
            ["/retention", "Retention Engine", HeartHandshake],
        ],
    ],
    [
        "Commerce",
        [
            ["/sales", "Sales", ReceiptIndianRupee],
            ["/custom-orders", "Custom Orders", Gem],
            ["/offers", "Offers", Tags],
            ["/loyalty", "Loyalty", Sparkles],
            ["/gift-cards", "Gift Cards", Gift],
            ["/privilege-cards", "Privilege Cards", CreditCard],
        ],
    ],
    [
        "Growth",
        [
            ["/exhibitions", "Events & Exhibitions", CalendarDays],
            ["/marketing", "Marketing Campaigns", Megaphone],
            ["/inbox", "WhatsApp & Insta Inbox", MessagesSquare],
            ["/ad-campaigns", "Instagram & Meta Ads", Camera],
            ["/video-sales", "Video Call Sales", Video],
            ["/festival-campaigns", "Festival Campaigns", CalendarDays],
            ["/reports", "Reports", BarChart3],
        ],
    ],
    [
        "Operations",
        [
            ["/smart-tasks", "All Smart Tasks", CheckSquare],
            ["/task-rules", "Task Rules", SlidersHorizontal],
            ["/message-templates", "Message Templates", Megaphone],
            ["/tasks", "Manual Tasks", CheckSquare],
            ["/rewards", "My Rewards", Award],
            ["/notifications", "Notifications", Bell],
        ],
    ],
    [
        "Administration",
        [
            ["/staff", "Staff Management", UserCog],
            ["/category-rules", "Customer Categories", SlidersHorizontal],
            ["/settings", "Settings", Settings],
        ],
    ],
];
const permissionModule = (path) => ({
    "/": "dashboard", "/smart-work": "tasks", "/smart-tasks": "tasks", "/task-rules": "tasks",
    "/message-templates": "marketing", "/tasks": "tasks", "/privilege-cards": "customers",
    "/festival-campaigns": "marketing", "/rewards": "rewards", "/notifications": "notifications",
    "/inbox": "inbox", "/ad-campaigns": "ads", "/video-sales": "video-calls",
    "/staff": "staff", "/settings": "settings", "/category-rules": "settings",
}[path] || path.slice(1));
export default function Layout() {
    const [open, setOpen] = useState(false);
    const { user, logout, hasPermission } = useAuth();
    const loc = useLocation();
    const current =
        groups.flatMap((g) => g[1]).find((x) => x[0] === loc.pathname)?.[1] ||
        "Jewellery CRM";
    return (
        <div className="min-h-screen lg:flex">
            <aside
                className={`fixed inset-y-0 left-0 z-40 w-72 border-r border-[#39362f] bg-[#080808] text-white transition-transform lg:translate-x-0 ${open ? "translate-x-0" : "-translate-x-full"}`}
            >
                <div className="flex h-20 items-center justify-between border-b border-white/10 px-6">
                    <img
                        src="/crm/kalasha-logo.png"
                        alt="Kalasha Fine Jewels"
                        className="h-14 w-auto object-contain"
                    />
                    <button
                        className="lg:hidden"
                        onClick={() => setOpen(false)}
                    >
                        <X />
                    </button>
                </div>
                <nav className="h-[calc(100vh-5rem)] overflow-y-auto px-3 py-5">
                    {groups.map(([group, items]) => (
                        <div key={group} className="mb-5">
                            <div className="mb-2 px-3 text-[10px] font-semibold uppercase tracking-[.18em] text-white/30">
                                {group}
                            </div>
                            {items.filter(([to])=>hasPermission(`${permissionModule(to)}.view`)).map(([to, label, Icon]) => (
                                <NavLink
                                    key={to}
                                    to={to}
                                    end={to === "/"}
                                    onClick={() => setOpen(false)}
                                    className={({ isActive }) =>
                                        `mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm ${isActive ? "bg-[#b58b36] text-white" : "text-white/65 hover:bg-white/5 hover:text-white"}`
                                    }
                                >
                                    <Icon size={18} />
                                    {label}
                                </NavLink>
                            ))}
                        </div>
                    ))}
                </nav>
            </aside>
            <div className="min-w-0 flex-1 lg:ml-72">
                <header className="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-[#e5dfd2] bg-[#f8f6f1]/90 px-5 backdrop-blur-xl md:px-8">
                    <div className="flex items-center gap-3">
                        <button
                            className="rounded-lg p-2 hover:bg-white lg:hidden"
                            onClick={() => setOpen(true)}
                        >
                            <Menu />
                        </button>
                        <img
                            src="/crm/kalasha-logo.png"
                            alt="Kalasha Fine Jewels"
                            className="hidden h-10 w-auto object-contain sm:block"
                        />
                        <div className="border-l border-[#ded8cb] pl-3">
                            <h1 className="font-serif text-xl font-semibold">
                                {current}
                            </h1>
                            <p className="text-[9px] uppercase tracking-[.2em] text-[#a08348]">
                                Jewellery CRM
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                        <NavLink to="/notifications" className="relative rounded-full border border-[#ded8cb] bg-white p-2.5">
                            <Bell size={18} />
                            <i className="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-[#b58b36]" />
                        </NavLink>
                        <div className="hidden items-center gap-3 border-l border-[#ded8cb] pl-3 sm:flex">
                            <div className="flex h-10 w-10 items-center justify-center rounded-full bg-[#2c2923] font-serif text-lg text-[#e5c984]">
                                {user?.name?.[0]}
                            </div>
                            <div>
                                <div className="text-sm font-semibold">
                                    {user?.name}
                                </div>
                                <button
                                    onClick={logout}
                                    className="text-xs text-[#827a6e] hover:text-[#b58b36]"
                                >
                                    {user?.role?.name || "CRM User"} · Sign out
                                </button>
                            </div>
                            <ChevronDown size={15} className="text-[#9c9487]" />
                        </div>
                    </div>
                </header>
                <main className="p-5 md:p-8">
                    <Outlet />
                </main>
            </div>
        </div>
    );
}
