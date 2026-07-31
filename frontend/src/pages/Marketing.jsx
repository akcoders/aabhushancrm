/* eslint-disable react-hooks/set-state-in-effect */
import { useEffect, useState } from "react";
import { useSearchParams } from "react-router-dom";
import {
    BarChart3,
    CheckCircle2,
    Eye,
    Mail,
    Megaphone,
    MessageCircle,
    Plus,
    RefreshCw,
    Send,
    Target,
    Users,
} from "lucide-react";
import api from "../api";
import { Badge, ErrorText, Loading, Modal } from "../components/UI";
import { money } from "../config";
const segments = [
    ["customers", "All consenting customers"],
    ["leads", "All consenting leads"],
    ["event-visitors", "Visitors from one exhibition"],
    ["returning-visitors", "Returning exhibition visitors"],
    ["vip", "VIP & HNI patrons"],
    ["dormant", "Dormant customers / win-back"],
    ["birthday", "Birthdays next month"],
    ["anniversary", "Anniversaries next month"],
];
const templates = {
    Revisit: {
        subject: "A private jewellery preview, curated for you",
        message:
            "Hello {{name}}, we remember the pieces and styles you loved. We have curated a private preview around your preferences. Reply YES and your consultant will reserve a convenient appointment.",
    },
    "Thank You": {
        subject: "Thank you for visiting us",
        message:
            "Hello {{name}}, thank you for spending time with us. Your preferences are safely noted, so you will never need to start the conversation again. We are here whenever you need guidance or jewellery care.",
    },
    "Win Back": {
        subject: "A complimentary jewellery care invitation",
        message:
            "Dear {{name}}, it has been a while. We would be delighted to clean and inspect your jewellery with our compliments—no purchase expected. Reply to choose a convenient time.",
    },
    "Offer Promotion": {
        subject: "A privilege selected for you",
        message:
            "Hello {{name}}, based on your jewellery interests, we selected {{offer}} for you. Your consultant can explain the details and help you use it thoughtfully.",
    },
};
const initial = {
    name: "",
    objective: "Revisit",
    channels: ["WhatsApp"],
    provider: "Interakt",
    template_name: "",
    template_language: "en",
    media_url: "",
    offer_id: "",
    exhibition_id: "",
    audience_rules: {
        audience: "returning-visitors",
        exhibition_id: "",
        days: 180,
        interest: "",
        category: "",
    },
    segment_name: "",
    subject: templates.Revisit.subject,
    message: templates.Revisit.message,
    scheduled_at: "",
};
export default function Marketing() {
    const [params] = useSearchParams();
    const [dash, setDash] = useState(),
        [campaigns, setCampaigns] = useState([]),
        [offers, setOffers] = useState([]),
        [events, setEvents] = useState([]),
        [show, setShow] = useState(false),
        [form, setForm] = useState(initial),
        [preview, setPreview] = useState(),
        [error, setError] = useState(),
        [busy, setBusy] = useState(false);
    const load = () =>
        Promise.all([
            api.get("/marketing/dashboard"),
            api.get("/marketing-campaigns"),
            api.get("/offers", { params: { per_page: 100 } }),
            api.get("/exhibitions", { params: { per_page: 100 } }),
        ]).then(([d, c, o, e]) => {
            setDash(d.data);
            setCampaigns(c.data.data);
            setOffers(o.data.data);
            setEvents(e.data.data);
        });
    useEffect(() => {
        load();
    }, []);
    useEffect(() => {
        if (params.get("event") && events.length) {
            const event = events.find(
                (x) => String(x.id) === params.get("event"),
            );
            setForm((f) => ({
                ...f,
                name: `${event?.name || "Event"} Revisit Campaign`,
                exhibition_id: params.get("event"),
                audience_rules: {
                    ...f.audience_rules,
                    audience: "event-visitors",
                    exhibition_id: params.get("event"),
                },
            }));
            setShow(true);
        }
    }, [events, params]);
    if (!dash) return <Loading />;
    const channel = (x) =>
        setForm({
            ...form,
            channels: form.channels.includes(x)
                ? form.channels.filter((c) => c !== x)
                : [...form.channels, x],
        });
    const setRule = (k, v) =>
        setForm({
            ...form,
            audience_rules: { ...form.audience_rules, [k]: v },
        });
    const selectTemplate = (k) =>
        setForm({
            ...form,
            objective: k,
            subject: templates[k].subject,
            message: templates[k].message,
        });
    const doPreview = async () => {
        setBusy(true);
        setError();
        try {
            const { data } = await api.post("/marketing/preview", {
                channels: form.channels,
                audience_rules: form.audience_rules,
            });
            setPreview(data);
        } catch (e) {
            setError(e);
        } finally {
            setBusy(false);
        }
    };
    const save = async (launch) => {
        setBusy(true);
        setError();
        try {
            const { data } = await api.post("/marketing-campaigns", {
                ...form,
                offer_id: form.offer_id || null,
                exhibition_id: form.exhibition_id || null,
                scheduled_at: form.scheduled_at || null,
                segment_name: segments.find(
                    (x) => x[0] === form.audience_rules.audience,
                )?.[1],
            });
            if (launch)
                await api.post(`/marketing-campaigns/${data.id}/launch`);
            setShow(false);
            setPreview();
            setForm(initial);
            await load();
        } catch (e) {
            setError(e);
        } finally {
            setBusy(false);
        }
    };
    const launch = async (c) => {
        if (
            confirm(
                `Send "${c.name}" now to ${c.recipients_count || c.estimated_audience} consenting recipients?`,
            )
        ) {
            await api.post(`/marketing-campaigns/${c.id}/launch`);
            load();
        }
    };
    const s = dash.summary;
    return (
        <div>
            <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[.16em] text-[#aa8131]">
                        Data-driven relationships
                    </p>
                    <h2 className="page-title mt-1">
                        Marketing Command Center
                    </h2>
                    <p className="mt-1 max-w-2xl text-sm text-[#857d70]">
                        Reuse consented customer intelligence to create relevant
                        revisits—without turning trust into spam.
                    </p>
                </div>
                <button onClick={() => setShow(true)} className="btn-primary">
                    <Plus size={17} />
                    Create campaign
                </button>
            </div>
            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
                <Metric
                    icon={Megaphone}
                    label="Campaigns"
                    value={s.total_campaigns}
                />
                <Metric icon={Send} label="Sent" value={s.sent} />
                <Metric
                    icon={CheckCircle2}
                    label="Delivered"
                    value={s.delivered}
                />
                <Metric
                    icon={MessageCircle}
                    label="Replies"
                    value={s.replies}
                />
                <Metric
                    icon={Target}
                    label="Conversions"
                    value={s.conversions}
                />
                <Metric
                    icon={BarChart3}
                    label="Attributed revenue"
                    value={money(s.revenue)}
                    dark
                />
            </div>
            <div className="mt-5 grid gap-5 xl:grid-cols-[1.35fr_.65fr]">
                <div className="card overflow-hidden">
                    <div className="flex items-center justify-between border-b border-[#eee8dc] px-6 py-5">
                        <div>
                            <h3 className="font-serif text-xl font-semibold">
                                Campaign performance
                            </h3>
                            <p className="text-xs text-[#8b8376]">
                                Every message remains tied to an audience,
                                offer, and outcome.
                            </p>
                        </div>
                        {form.channels.includes("WhatsApp") && (
                            <div className="grid gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 md:grid-cols-3">
                                <label>
                                    <span className="label">Interakt template *</span>
                                    <input className="field" value={form.template_name} onChange={(e) => setForm({...form, template_name:e.target.value})} placeholder="approved_template_name" />
                                </label>
                                <label>
                                    <span className="label">Language</span>
                                    <input className="field" value={form.template_language} onChange={(e) => setForm({...form, template_language:e.target.value})} />
                                </label>
                                <label>
                                    <span className="label">Public image URL</span>
                                    <input className="field" value={form.media_url} onChange={(e) => setForm({...form, media_url:e.target.value})} placeholder="https://..." />
                                </label>
                            </div>
                        )}
                        <button
                            onClick={load}
                            className="rounded-lg p-2 hover:bg-[#f5f1e8]"
                        >
                            <RefreshCw size={16} />
                        </button>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[850px] text-left">
                            <thead>
                                <tr className="bg-[#fcfbf8] text-[10px] uppercase tracking-wider text-[#8b8376]">
                                    <th className="px-5 py-3">Campaign</th>
                                    <th>Channel</th>
                                    <th>Audience</th>
                                    <th>Delivered</th>
                                    <th>Engagement</th>
                                    <th>Conversions</th>
                                    <th>Revenue</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {campaigns.map((c) => {
                                    const engagement = c.delivered_count
                                        ? Math.round(
                                              ((c.clicked_count +
                                                  c.replied_count) /
                                                  c.delivered_count) *
                                                  100,
                                          )
                                        : 0;
                                    return (
                                        <tr
                                            key={c.id}
                                            className="border-t border-[#eee9df]"
                                        >
                                            <td className="px-5 py-4">
                                                <b className="text-sm">
                                                    {c.name}
                                                </b>
                                                <p className="text-xs text-[#8b8376]">
                                                    {c.objective} ·{" "}
                                                    {c.offer?.title ||
                                                        "No discount attached"}
                                                </p>
                                            </td>
                                            <td>
                                                <div className="flex gap-1">
                                                    {c.channels.map((x) =>
                                                        x === "WhatsApp" ? (
                                                            <MessageCircle
                                                                key={x}
                                                                size={15}
                                                                className="text-emerald-600"
                                                            />
                                                        ) : (
                                                            <Mail
                                                                key={x}
                                                                size={15}
                                                                className="text-blue-600"
                                                            />
                                                        ),
                                                    )}
                                                </div>
                                            </td>
                                            <td className="text-sm">
                                                {c.recipients_count ||
                                                    c.estimated_audience}
                                            </td>
                                            <td className="text-sm">
                                                {c.delivered_count}
                                            </td>
                                            <td className="text-sm">
                                                {engagement}%
                                            </td>
                                            <td className="text-sm">
                                                {c.converted_count}
                                            </td>
                                            <td className="text-sm font-semibold">
                                                {money(c.attributed_revenue)}
                                            </td>
                                            <td>
                                                {c.status === "Draft" ? (
                                                    <button
                                                        onClick={() =>
                                                            launch(c)
                                                        }
                                                        className="btn-secondary py-1.5"
                                                    >
                                                        <Send size={13} />
                                                        Launch
                                                    </button>
                                                ) : (
                                                    <Badge>{c.status}</Badge>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </div>
                <div className="space-y-5">
                    <div className="card p-6">
                        <h3 className="font-serif text-xl font-semibold">
                            Trust-led playbook
                        </h3>
                        {[
                            [
                                "1",
                                "Remember context",
                                "Use event history and interests so customers never repeat themselves.",
                            ],
                            [
                                "2",
                                "Give before asking",
                                "Care advice, cleaning, previews and education build more trust than constant discounts.",
                            ],
                            [
                                "3",
                                "Measure revisits",
                                "Track replies, appointments, conversions and revenue—not vanity send counts.",
                            ],
                            [
                                "4",
                                "Respect consent",
                                "WhatsApp and email opt-outs are automatically excluded.",
                            ],
                        ].map(([n, h, t]) => (
                            <div className="mt-4 flex gap-3" key={n}>
                                <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#f2ead8] text-xs font-bold text-[#9b7228]">
                                    {n}
                                </span>
                                <div>
                                    <b className="text-sm">{h}</b>
                                    <p className="text-xs leading-5 text-[#81796d]">
                                        {t}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                    <div className="card bg-[#29261f] p-6 text-white">
                        <Eye className="text-[#d7b967]" />
                        <h3 className="mt-4 font-serif text-xl">
                            Best next audience
                        </h3>
                        <p className="mt-2 text-sm leading-6 text-white/55">
                            Returning exhibition visitors recognize your brand
                            and already have preferences on record. Invite them
                            to continue the conversation—not restart it.
                        </p>
                        <button
                            onClick={() => {
                                setRule("audience", "returning-visitors");
                                setShow(true);
                            }}
                            className="mt-4 text-sm font-semibold text-[#e1c87d]"
                        >
                            Build revisit campaign →
                        </button>
                    </div>
                </div>
            </div>
            {show && (
                <CampaignBuilder
                    form={form}
                    setForm={setForm}
                    offers={offers}
                    events={events}
                    preview={preview}
                    error={error}
                    busy={busy}
                    channel={channel}
                    setRule={setRule}
                    selectTemplate={selectTemplate}
                    doPreview={doPreview}
                    save={save}
                    close={() => {
                        setShow(false);
                        setPreview();
                    }}
                />
            )}
        </div>
    );
}
function Metric({ icon: Icon, label, value, dark }) {
    return (
        <div className={`card p-4 ${dark ? "bg-[#29261f] text-white" : ""}`}>
            <Icon
                size={17}
                className={dark ? "text-[#d7b967]" : "text-[#b58b36]"}
            />
            <div className="mt-3 text-xl font-bold">{value}</div>
            <div
                className={`text-[10px] uppercase tracking-wider ${dark ? "text-white/45" : "text-[#81796d]"}`}
            >
                {label}
            </div>
        </div>
    );
}
function CampaignBuilder({
    form,
    setForm,
    offers,
    events,
    preview,
    error,
    busy,
    channel,
    setRule,
    selectTemplate,
    doPreview,
    save,
    close,
}) {
    return (
        <Modal title="Create WhatsApp / Email Campaign" onClose={close} wide>
            <ErrorText error={error} />
            <div className="grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
                <div className="space-y-5">
                    <label>
                        <span className="label">Campaign name *</span>
                        <input
                            className="field"
                            value={form.name}
                            onChange={(e) =>
                                setForm({ ...form, name: e.target.value })
                            }
                            placeholder="e.g. Bridal Expo Private Revisit"
                        />
                    </label>
                    <div>
                        <span className="label">
                            Campaign purpose & message style
                        </span>
                        <div className="flex flex-wrap gap-2">
                            {Object.keys(templates).map((x) => (
                                <button
                                    type="button"
                                    key={x}
                                    onClick={() => selectTemplate(x)}
                                    className={`rounded-full border px-3 py-2 text-xs font-semibold ${form.objective === x ? "border-[#b58b36] bg-[#f4ecd8] text-[#8d6724]" : "border-[#ddd6c9]"}`}
                                >
                                    {x}
                                </button>
                            ))}
                        </div>
                    </div>
                    <div>
                        <span className="label">Delivery channels *</span>
                        <div className="grid grid-cols-2 gap-3">
                            <button
                                onClick={() => channel("WhatsApp")}
                                className={`rounded-xl border p-4 text-left ${form.channels.includes("WhatsApp") ? "border-emerald-500 bg-emerald-50" : "border-[#ddd6c9]"}`}
                            >
                                <MessageCircle className="mb-2 text-emerald-600" />
                                <b className="text-sm">WhatsApp</b>
                                <p className="text-[10px] text-[#81796d]">
                                    Only opted-in mobile numbers
                                </p>
                            </button>
                            <button
                                onClick={() => channel("Email")}
                                className={`rounded-xl border p-4 text-left ${form.channels.includes("Email") ? "border-blue-500 bg-blue-50" : "border-[#ddd6c9]"}`}
                            >
                                <Mail className="mb-2 text-blue-600" />
                                <b className="text-sm">Email</b>
                                <p className="text-[10px] text-[#81796d]">
                                    Only opted-in email addresses
                                </p>
                            </button>
                        </div>
                    </div>
                    <label>
                        <span className="label">Audience segment *</span>
                        <select
                            className="field"
                            value={form.audience_rules.audience}
                            onChange={(e) =>
                                setRule("audience", e.target.value)
                            }
                        >
                            {segments.map(([v, l]) => (
                                <option key={v} value={v}>
                                    {l}
                                </option>
                            ))}
                        </select>
                    </label>
                    {form.audience_rules.audience === "event-visitors" && (
                        <label>
                            <span className="label">Source exhibition</span>
                            <select
                                className="field"
                                value={form.audience_rules.exhibition_id}
                                onChange={(e) => {
                                    setRule("exhibition_id", e.target.value);
                                    setForm((f) => ({
                                        ...f,
                                        exhibition_id: e.target.value,
                                    }));
                                }}
                            >
                                <option value="">Select exhibition</option>
                                {events.map((x) => (
                                    <option key={x.id} value={x.id}>
                                        {x.name}
                                    </option>
                                ))}
                            </select>
                        </label>
                    )}
                    {form.audience_rules.audience === "dormant" && (
                        <label>
                            <span className="label">No purchase for</span>
                            <select
                                className="field"
                                value={form.audience_rules.days}
                                onChange={(e) =>
                                    setRule("days", e.target.value)
                                }
                            >
                                <option value="90">90 days</option>
                                <option value="180">180 days</option>
                                <option value="365">1 year</option>
                            </select>
                        </label>
                    )}
                    <div className="grid gap-4 md:grid-cols-2">
                        <label>
                            <span className="label">Filter by interest</span>
                            <select
                                className="field"
                                value={form.audience_rules.interest}
                                onChange={(e) =>
                                    setRule("interest", e.target.value)
                                }
                            >
                                <option value="">Any interest</option>
                                {[
                                    "Bridal jewellery",
                                    "Gold jewellery",
                                    "Diamond jewellery",
                                    "Silver jewellery",
                                    "Polki",
                                    "Kundan",
                                    "Custom design",
                                ].map((x) => (
                                    <option key={x}>{x}</option>
                                ))}
                            </select>
                        </label>
                        <label>
                            <span className="label">Linked offer</span>
                            <select
                                className="field"
                                value={form.offer_id}
                                onChange={(e) =>
                                    setForm({
                                        ...form,
                                        offer_id: e.target.value,
                                    })
                                }
                            >
                                <option value="">
                                    No offer - relationship message
                                </option>
                                {offers.map((x) => (
                                    <option key={x.id} value={x.id}>
                                        {x.title}
                                    </option>
                                ))}
                            </select>
                        </label>
                    </div>
                    <label>
                        <span className="label">Email subject</span>
                        <input
                            className="field"
                            value={form.subject}
                            onChange={(e) =>
                                setForm({ ...form, subject: e.target.value })
                            }
                        />
                    </label>
                        <label>
                            <span className="label">Personalized message *</span>
                        <textarea
                            rows="6"
                            className="field"
                            value={form.message}
                            onChange={(e) =>
                                setForm({ ...form, message: e.target.value })
                            }
                        />
                        <p className="mt-1 text-[10px] text-[#91897c]">
                            Use {"{{name}}"} and {"{{offer}}"} placeholders.
                            Keep trust before urgency.
                            </p>
                        </label>
                        <label>
                            <span className="label">Schedule (optional)</span>
                            <input
                                type="datetime-local"
                                className="field"
                                value={form.scheduled_at}
                                onChange={(e) =>
                                    setForm({
                                        ...form,
                                        scheduled_at: e.target.value,
                                    })
                                }
                            />
                            <p className="mt-1 text-[10px] text-[#91897c]">
                                Leave empty to send immediately. Server cron dispatches scheduled campaigns.
                            </p>
                        </label>
                </div>
                <div>
                    <div className="sticky top-0 rounded-2xl border border-[#e4ddcf] bg-[#faf8f3] p-5">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="font-serif text-xl font-semibold">
                                    Audience preview
                                </h3>
                                <p className="text-xs text-[#81796d]">
                                    Consent is checked automatically.
                                </p>
                            </div>
                            <button
                                onClick={doPreview}
                                className="btn-secondary"
                            >
                                <Users size={15} />
                                {busy ? "Checking..." : "Calculate"}
                            </button>
                        </div>
                        {preview ? (
                            <div>
                                <div className="my-5 rounded-xl bg-[#29261f] p-4 text-white">
                                    <div className="text-3xl font-bold">
                                        {preview.count}
                                    </div>
                                    <div className="text-xs text-white/50">
                                        eligible, consenting recipients
                                    </div>
                                </div>
                                <div className="max-h-64 overflow-y-auto">
                                    {preview.recipients.map((x) => (
                                        <div
                                            key={`${x.type}-${x.id}`}
                                            className="flex items-center justify-between border-b border-[#e8e1d4] py-2.5"
                                        >
                                            <div>
                                                <b className="text-xs">
                                                    {x.name}
                                                </b>
                                                <p className="text-[10px] text-[#8d8578]">
                                                    {x.mobile || x.email}
                                                </p>
                                            </div>
                                            <Badge>{x.type}</Badge>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        ) : (
                            <div className="my-8 text-center text-sm text-[#8b8376]">
                                <Target className="mx-auto mb-2 text-[#b58b36]" />
                                Choose your segment, then calculate the
                                reachable audience.
                            </div>
                        )}
                        <div className="mt-5 grid grid-cols-2 gap-2">
                            <button
                                disabled={!preview?.count || busy}
                                onClick={() => save(false)}
                                className="btn-secondary"
                            >
                                Save draft
                            </button>
                            <button
                                disabled={!preview?.count || busy}
                                onClick={() => save(true)}
                                className="btn-primary"
                            >
                                <Send size={15} />
                                Save & send
                            </button>
                        </div>
                        <p className="mt-3 text-center text-[10px] leading-4 text-[#91897c]">
                            Sending creates auditable communication logs.
                            Connect an approved WhatsApp Business/email provider
                            for production delivery.
                        </p>
                    </div>
                </div>
            </div>
        </Modal>
    );
}
