import { useCallback, useEffect, useMemo, useState } from "react";
import {
    CreditCard,
    Edit3,
    Plus,
    Search,
    ShieldCheck,
    Sparkles,
    Trash2,
    Wifi,
} from "lucide-react";
import api from "../api";
import { Badge, Empty, ErrorText, Loading, Modal } from "../components/UI";
import { money } from "../config";
import useCompanyName from "../useCompanyName";
import SearchableSelect from "../components/SearchableSelect";

const tiers = ["Silver", "Gold", "Platinum", "Diamond"];
const statuses = ["Active", "Suspended", "Expired", "Cancelled"];
const today = () => new Date().toISOString().slice(0, 10);

export default function PrivilegeCards() {
    const companyName = useCompanyName();
    const [cards, setCards] = useState();
    const [customers, setCustomers] = useState([]);
    const [search, setSearch] = useState("");
    const [tier, setTier] = useState("");
    const [status, setStatus] = useState("");
    const [editing, setEditing] = useState();
    const [form, setForm] = useState({});
    const [error, setError] = useState();
    const [busy, setBusy] = useState(false);

    const load = useCallback(() => {
        api.get("/privilege-cards", {
            params: { search, tier, status, per_page: 100 },
        }).then((response) => setCards(response.data.data));
    }, [search, tier, status]);

    useEffect(() => {
        const timer = setTimeout(load, 200);
        return () => clearTimeout(timer);
    }, [load]);

    useEffect(() => {
        api.get("/customers", {
            params: { per_page: 100, sort: "name", direction: "asc" },
        }).then((response) => setCustomers(response.data.data));
    }, []);

    const totals = useMemo(
        () => ({
            active:
                cards?.filter((card) => card.status === "Active").length || 0,
            value:
                cards
                    ?.filter((card) => card.status === "Active")
                    .reduce((sum, card) => sum + Number(card.amount), 0) || 0,
            diamond:
                cards?.filter(
                    (card) =>
                        card.tier === "Diamond" && card.status === "Active",
                ).length || 0,
        }),
        [cards],
    );

    const open = (card) => {
        setEditing(card || {});
        setForm(
            card
                ? {
                      customer_id: card.customer_id,
                      tier: card.tier,
                      amount: card.amount,
                      issued_at: card.issued_at?.slice(0, 10),
                      expires_at: card.expires_at?.slice(0, 10) || "",
                      status: card.status,
                      notes: card.notes || "",
                  }
                : {
                      tier: "Gold",
                      amount: "",
                      issued_at: today(),
                      expires_at: "",
                      status: "Active",
                      notes: "",
                  },
        );
        setError();
    };

    const save = async (event) => {
        event.preventDefault();
        setBusy(true);
        setError();
        try {
            editing?.id
                ? await api.put(`/privilege-cards/${editing.id}`, form)
                : await api.post("/privilege-cards", form);
            setEditing();
            load();
        } catch (exception) {
            setError(exception);
        } finally {
            setBusy(false);
        }
    };

    const remove = async (card) => {
        if (confirm(`Remove privilege card ${card.card_number}?`)) {
            await api.delete(`/privilege-cards/${card.id}`);
            load();
        }
    };

    return (
        <div>
            <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[.18em] text-[#aa8131]">
                        Exclusive customer recognition
                    </p>
                    <h2 className="page-title mt-1">Privilege Cards</h2>
                    <p className="mt-1 text-sm text-[#857d70]">
                        Create premium membership cards for your most valued
                        patrons.
                    </p>
                </div>
                <button className="btn-primary" onClick={() => open()}>
                    <Plus size={17} />
                    Issue privilege card
                </button>
            </div>

            <div className="mb-5 grid gap-4 sm:grid-cols-3">
                <Summary
                    icon={ShieldCheck}
                    label="Active cards"
                    value={totals.active}
                />
                <Summary
                    icon={CreditCard}
                    label="Active privilege value"
                    value={money(totals.value)}
                />
                <Summary
                    icon={Sparkles}
                    label="Diamond members"
                    value={totals.diamond}
                />
            </div>

            <div className="card mb-6 flex flex-wrap gap-3 p-4">
                <label className="relative min-w-60 flex-1">
                    <Search
                        size={17}
                        className="absolute left-3 top-2.5 text-[#9a9285]"
                    />
                    <input
                        className="field pl-9"
                        placeholder="Search card, customer, or mobile…"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                    />
                </label>
                <select
                    className="field w-auto min-w-36"
                    value={tier}
                    onChange={(event) => setTier(event.target.value)}
                >
                    <option value="">All tiers</option>
                    {tiers.map((item) => (
                        <option key={item}>{item}</option>
                    ))}
                </select>
                <select
                    className="field w-auto min-w-36"
                    value={status}
                    onChange={(event) => setStatus(event.target.value)}
                >
                    <option value="">All statuses</option>
                    {statuses.map((item) => (
                        <option key={item}>{item}</option>
                    ))}
                </select>
            </div>

            {!cards ? (
                <Loading />
            ) : !cards.length ? (
                <Empty
                    title="No privilege cards yet"
                    text="Issue the first premium card to a valued customer."
                />
            ) : (
                <div className="grid gap-6 md:grid-cols-2 2xl:grid-cols-3">
                    {cards.map((card) => (
                        <div key={card.id}>
                            <PrivilegeCardVisual
                                card={card}
                                companyName={companyName}
                            />
                            <div className="mx-3 flex items-center justify-between rounded-b-2xl border border-t-0 border-[#e8e1d3] bg-white px-4 py-3 shadow-[0_8px_24px_rgba(41,35,25,.06)]">
                                <div>
                                    <Badge>{card.status}</Badge>
                                    <span className="ml-2 text-xs text-[#827a6d]">
                                        {card.issuer?.name
                                            ? `Issued by ${card.issuer.name}`
                                            : "CRM issued"}
                                    </span>
                                </div>
                                <div className="flex gap-1">
                                    <button
                                        title="Edit card"
                                        onClick={() => open(card)}
                                        className="rounded-lg p-2 text-[#766d5e] hover:bg-[#f5f0e5]"
                                    >
                                        <Edit3 size={15} />
                                    </button>
                                    <button
                                        title="Remove card"
                                        onClick={() => remove(card)}
                                        className="rounded-lg p-2 text-red-500 hover:bg-red-50"
                                    >
                                        <Trash2 size={15} />
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {editing && (
                <Modal
                    title={
                        editing.id
                            ? "Edit Privilege Card"
                            : "Issue Privilege Card"
                    }
                    onClose={() => setEditing()}
                    wide
                >
                    <ErrorText error={error} />
                    <form onSubmit={save}>
                        <div className="grid gap-6 lg:grid-cols-[1fr_1.05fr]">
                            <div className="grid content-start gap-4 md:grid-cols-2">
                                <label className="md:col-span-2">
                                    <span className="label">Customer *</span>
                                    <SearchableSelect
                                        required
                                        value={form.customer_id || ""}
                                        options={customers.map((customer) => ({
                                            value: customer.id,
                                            label: `${customer.name} · ${customer.mobile || "No mobile"} · ${customer.customer_code || "Customer"}`,
                                            search: customer.email || "",
                                        }))}
                                        placeholder="Search customer name, mobile, email or code..."
                                        onChange={(customerId) =>
                                            setForm({
                                                ...form,
                                                customer_id: customerId,
                                            })
                                        }
                                    />
                                </label>
                                <label>
                                    <span className="label">Card tier *</span>
                                    <select
                                        required
                                        className="field"
                                        value={form.tier || ""}
                                        onChange={(event) =>
                                            setForm({
                                                ...form,
                                                tier: event.target.value,
                                            })
                                        }
                                    >
                                        {tiers.map((item) => (
                                            <option key={item}>{item}</option>
                                        ))}
                                    </select>
                                </label>
                                <label>
                                    <span className="label">
                                        Privilege amount *
                                    </span>
                                    <input
                                        required
                                        min="0"
                                        step="0.01"
                                        type="number"
                                        className="field"
                                        value={form.amount || ""}
                                        onChange={(event) =>
                                            setForm({
                                                ...form,
                                                amount: event.target.value,
                                            })
                                        }
                                    />
                                </label>
                                <label>
                                    <span className="label">Issue date *</span>
                                    <input
                                        required
                                        type="date"
                                        className="field"
                                        value={form.issued_at || ""}
                                        onChange={(event) =>
                                            setForm({
                                                ...form,
                                                issued_at: event.target.value,
                                            })
                                        }
                                    />
                                </label>
                                <label>
                                    <span className="label">Expiry date</span>
                                    <input
                                        type="date"
                                        className="field"
                                        value={form.expires_at || ""}
                                        onChange={(event) =>
                                            setForm({
                                                ...form,
                                                expires_at: event.target.value,
                                            })
                                        }
                                    />
                                </label>
                                <label>
                                    <span className="label">Status *</span>
                                    <select
                                        required
                                        className="field"
                                        value={form.status || ""}
                                        onChange={(event) =>
                                            setForm({
                                                ...form,
                                                status: event.target.value,
                                            })
                                        }
                                    >
                                        {statuses.map((item) => (
                                            <option key={item}>{item}</option>
                                        ))}
                                    </select>
                                </label>
                                <label className="md:col-span-2">
                                    <span className="label">
                                        Internal notes
                                    </span>
                                    <textarea
                                        rows="3"
                                        className="field"
                                        value={form.notes || ""}
                                        onChange={(event) =>
                                            setForm({
                                                ...form,
                                                notes: event.target.value,
                                            })
                                        }
                                    />
                                </label>
                            </div>
                            <div>
                                <p className="label">Live card preview</p>
                                <PrivilegeCardVisual
                                    companyName={companyName}
                                    card={{
                                        card_number:
                                            editing.card_number ||
                                            "PRV-PREVIEW",
                                        customer: customers.find(
                                            (customer) =>
                                                String(customer.id) ===
                                                String(form.customer_id),
                                        ) || { name: "Valued Customer" },
                                        tier: form.tier || "Gold",
                                        amount: form.amount || 0,
                                        issued_at: form.issued_at,
                                        expires_at: form.expires_at,
                                    }}
                                />
                                <p className="mt-3 text-xs leading-5 text-[#857d70]">
                                    The card number is generated automatically
                                    when you issue the card.
                                </p>
                            </div>
                        </div>
                        <div className="mt-6 flex justify-end gap-2 border-t border-[#eee8dc] pt-5">
                            <button
                                type="button"
                                className="btn-secondary"
                                onClick={() => setEditing()}
                            >
                                Cancel
                            </button>
                            <button disabled={busy} className="btn-primary">
                                {busy
                                    ? "Saving…"
                                    : editing.id
                                      ? "Save changes"
                                      : "Issue card"}
                            </button>
                        </div>
                    </form>
                </Modal>
            )}
        </div>
    );
}

export function PrivilegeCardVisual({
    card,
    compact = false,
    companyName = "Your Company",
}) {
    const theme =
        {
            Silver: {
                accent: "#d9dde3",
                background:
                    "radial-gradient(circle at 88% 10%, rgba(255,255,255,.16), transparent 28%), linear-gradient(135deg, #111317 0%, #30343b 48%, #121419 100%)",
            },
            Gold: {
                accent: "#d5ad4b",
                background:
                    "radial-gradient(circle at 88% 10%, rgba(213,173,75,.17), transparent 30%), linear-gradient(135deg, #080807 0%, #211b0e 48%, #090806 100%)",
            },
            Platinum: {
                accent: "#c7ccd3",
                background:
                    "radial-gradient(circle at 88% 10%, rgba(216,224,235,.16), transparent 28%), linear-gradient(135deg, #090b0e 0%, #242932 50%, #0b0d11 100%)",
            },
            Diamond: {
                accent: "#8bd8e4",
                background:
                    "radial-gradient(circle at 88% 10%, rgba(105,218,232,.17), transparent 28%), linear-gradient(135deg, #061014 0%, #0d3038 50%, #071216 100%)",
            },
        }[card.tier] || {
            accent: "#d5ad4b",
            background:
                "linear-gradient(135deg, #080807 0%, #211b0e 48%, #090806 100%)",
        };
    const number = formatCardNumber(card.card_number);

    return (
        <div
            className={`relative isolate aspect-[1.586/1] w-full overflow-hidden rounded-[24px] p-6 text-white shadow-[0_24px_60px_rgba(14,12,8,.28)] ${compact ? "max-w-md" : ""}`}
            style={{
                background: theme.background,
                "--card-accent": theme.accent,
            }}
        >
            <div
                className="pointer-events-none absolute inset-[5px] rounded-[20px] border opacity-45"
                style={{ borderColor: theme.accent }}
            />
            <div className="pointer-events-none absolute inset-0 bg-[linear-gradient(112deg,transparent_15%,rgba(255,255,255,.055)_38%,transparent_55%)]" />
            <div
                className="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rotate-12 rounded-[42%] border opacity-10"
                style={{ borderColor: theme.accent }}
            />
            <img
                src="/crm/kalasha-logo.png"
                alt=""
                aria-hidden="true"
                className="pointer-events-none absolute inset-0 m-auto w-[66%] object-contain opacity-[.055] grayscale"
            />
            <div className="relative flex h-full flex-col justify-between">
                <div className="flex items-start justify-between">
                    <img
                        src="/crm/kalasha-logo.png"
                        alt={companyName}
                        className="h-auto w-[116px] object-contain drop-shadow-sm"
                    />
                    <div className="text-right">
                        <p className="font-serif text-[17px] font-semibold tracking-[.08em]">
                            PRIVILEGE
                        </p>
                        <span
                            className="mt-1 inline-flex rounded-full border px-2.5 py-0.5 text-[8px] font-bold uppercase tracking-[.28em]"
                            style={{
                                borderColor: `${theme.accent}88`,
                                color: theme.accent,
                            }}
                        >
                            {card.tier || "Gold"} Member
                        </span>
                    </div>
                </div>

                <div>
                    <div className="mb-3 flex items-center gap-3">
                        <div className="relative h-9 w-12 overflow-hidden rounded-[6px] border border-white/25 bg-gradient-to-br from-[#f4dfa0] via-[#c79c3d] to-[#80601c] shadow-[inset_0_0_8px_rgba(255,255,255,.3)]">
                            <i className="absolute left-1/2 top-0 h-full w-px bg-black/20" />
                            <i className="absolute left-0 top-1/2 h-px w-full bg-black/20" />
                            <i className="absolute left-2 top-0 h-full w-px bg-black/15" />
                            <i className="absolute right-2 top-0 h-full w-px bg-black/15" />
                        </div>
                        <Wifi
                            size={19}
                            className="rotate-90 opacity-65"
                            strokeWidth={1.6}
                        />
                    </div>
                    <p className="mb-1 text-[7px] uppercase tracking-[.26em] text-white/40">
                        Member number
                    </p>
                    <p className="whitespace-nowrap font-mono text-[18px] font-medium tracking-[.16em] text-white drop-shadow-sm">
                        {number}
                    </p>
                </div>

                <div className="grid grid-cols-[1fr_auto_auto] items-end gap-5">
                    <div>
                        <p className="text-[7px] uppercase tracking-[.22em] text-white/40">
                            Member
                        </p>
                        <p className="mt-0.5 truncate font-serif text-[17px] font-semibold tracking-[.04em]">
                            {card.customer?.name || "Valued Customer"}
                        </p>
                    </div>
                    <div className="text-right">
                        <p className="text-[7px] uppercase tracking-[.22em] text-white/40">
                            Valid thru
                        </p>
                        <p className="mt-1 text-[11px] font-semibold tracking-[.08em]">
                            {card.expires_at
                                ? formatCardDate(card.expires_at)
                                : "LIFETIME"}
                        </p>
                    </div>
                    <div className="min-w-20 text-right">
                        <p className="text-[7px] uppercase tracking-[.22em] text-white/40">
                            Value
                        </p>
                        <p
                            className="mt-0.5 text-[16px] font-bold"
                            style={{ color: theme.accent }}
                        >
                            {money(card.amount)}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}

function Summary({ icon: Icon, label, value }) {
    return (
        <div className="card flex items-center gap-4 p-5">
            <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-[#f4ecda] text-[#a67c2b]">
                <Icon size={19} />
            </span>
            <div>
                <b className="text-xl">{value}</b>
                <p className="text-xs text-[#81796d]">{label}</p>
            </div>
        </div>
    );
}
function formatCardDate(value) {
    if (!value) return "—";
    const date = new Date(value);
    return date.toLocaleDateString("en-IN", {
        month: "2-digit",
        year: "2-digit",
    });
}
function formatCardNumber(value) {
    const digits = String(value || "0000000000000000")
        .replace(/\D/g, "")
        .padEnd(16, "0")
        .slice(0, 16);
    return digits.match(/.{1,4}/g).join(" ");
}
