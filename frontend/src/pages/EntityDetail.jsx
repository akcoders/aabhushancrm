import { useEffect, useState } from "react";
import { Link, useNavigate, useParams } from "react-router-dom";
import {
    ArrowLeft,
    CheckCircle2,
    ExternalLink,
    MessageSquare,
    Plus,
    UserRound,
} from "lucide-react";
import api from "../api";
import { Badge, ErrorText, Loading, Modal } from "../components/UI";
import { money } from "../config";
import { PrivilegeCardVisual } from "./PrivilegeCards";
import useCompanyName from "../useCompanyName";
const titleOf = (type, d) =>
    type === "leads"
        ? d.name
        : type === "customers"
          ? d.name
          : type === "custom-orders"
            ? d.order_number
            : d.name;
export default function EntityDetail({ type }) {
    const { id } = useParams();
    const nav = useNavigate();
    const [d, setD] = useState(),
        [note, setNote] = useState(""),
        [modal, setModal] = useState(false),
        [error, setError] = useState();
    const load = () =>
        api.get(`/${type}/${id}`).then((r) => setD(r.data.data || r.data));
    useEffect(load, [type, id]);
    if (!d) return <Loading />;
    const convert = async () => {
        try {
            const r = await api.post(`/leads/${id}/convert`);
            nav(`/customers/${r.data.customer.id}`);
        } catch (e) {
            setError(e);
        }
    };
    const addNote = async (e) => {
        e.preventDefault();
        await api.post(`/leads/${id}/notes`, { note });
        setNote("");
        setModal(false);
        load();
    };
    const status = async (s) => {
        await api.post(`/custom-orders/${id}/status`, { status: s });
        load();
    };
    return (
        <div>
            <Link
                to={`/${type}`}
                className="mb-5 inline-flex items-center gap-2 text-sm text-[#80786a] hover:text-[#a67c2b]"
            >
                <ArrowLeft size={17} />
                Back to {type.replace("-", " ")}
            </Link>
            <ErrorText error={error} />
            <div className="card mb-5 overflow-hidden">
                <div className="h-2 bg-gradient-to-r from-[#2d2922] via-[#b58b36] to-[#ead9ad]" />
                <div className="flex flex-wrap items-start justify-between gap-4 p-6 md:p-8">
                    <div className="flex items-start gap-4">
                        <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#f4ecda] font-serif text-3xl text-[#a57c2e]">
                            {titleOf(type, d)?.[0]}
                        </div>
                        <div>
                            <div className="mb-2 flex items-center gap-2">
                                <h2 className="page-title">
                                    {titleOf(type, d)}
                                </h2>
                                <Badge>{d.status || d.category}</Badge>
                            </div>
                            <p className="text-sm text-[#7e7669]">
                                {type === "leads" &&
                                    `${d.mobile} · ${d.source}`}
                                {type === "customers" &&
                                    `${d.customer_code} · ${d.mobile}`}
                                {type === "custom-orders" &&
                                    `${d.jewellery_type} in ${d.metal_type}`}
                                {type === "exhibitions" &&
                                    `${d.location} · Stall ${d.stall_number || "—"}`}
                            </p>
                        </div>
                    </div>
                    <div className="flex gap-2">
                        {type === "leads" && (
                            <>
                                <button
                                    onClick={() => setModal(true)}
                                    className="btn-secondary"
                                >
                                    <Plus size={16} />
                                    Add note
                                </button>
                                {d.status !== "Converted" && (
                                    <button
                                        onClick={convert}
                                        className="btn-primary"
                                    >
                                        <CheckCircle2 size={16} />
                                        Convert to customer
                                    </button>
                                )}
                            </>
                        )}
                        {type === "exhibitions" && (
                            <Link
                                className="btn-primary"
                                to={`/capture/${d.public_token}`}
                                target="_blank"
                            >
                                <ExternalLink size={16} />
                                Public capture form
                            </Link>
                        )}
                    </div>
                </div>
            </div>
            {type === "leads" && <LeadView d={d} />}{" "}
            {type === "customers" && <CustomerView d={d} />}{" "}
            {type === "custom-orders" && <OrderView d={d} onStatus={status} />}{" "}
            {type === "exhibitions" && <EventView d={d} />}{" "}
            {modal && (
                <Modal title="Add lead note" onClose={() => setModal(false)}>
                    <form onSubmit={addNote}>
                        <textarea
                            autoFocus
                            className="field"
                            rows="5"
                            value={note}
                            onChange={(e) => setNote(e.target.value)}
                            placeholder="Capture the conversation, preference, or next step…"
                            required
                        />
                        <div className="mt-4 flex justify-end">
                            <button className="btn-primary">
                                Add to timeline
                            </button>
                        </div>
                    </form>
                </Modal>
            )}
        </div>
    );
}
function Info({ label, value }) {
    return (
        <div>
            <div className="text-[10px] font-bold uppercase tracking-[.1em] text-[#9b9386]">
                {label}
            </div>
            <div className="mt-1 text-sm font-semibold">{value || "—"}</div>
        </div>
    );
}
function LeadView({ d }) {
    const noteItems = Array.isArray(d.notes) ? d.notes : [];
    const followupItems = Array.isArray(d.followups) ? d.followups : [];
    const historyItems = Array.isArray(d.history) ? d.history : [];
    const interests = normalizeList(d.product_interests);
    const profileNotes = typeof d.notes === "string" ? d.notes : null;
    const timeline = [
        ...noteItems.map((x) => ({ ...x, kind: "Note", text: x.note })),
        ...followupItems.map((x) => ({
            ...x,
            kind: x.type,
            text: x.notes,
            date: x.scheduled_at,
        })),
        ...historyItems.map((x) => ({
            ...x,
            kind: "Activity",
            text: x.action,
        })),
    ].sort(
        (a, b) =>
            new Date(b.date || b.created_at) - new Date(a.date || a.created_at),
    );
    return (
        <div className="grid gap-5 xl:grid-cols-[.8fr_1.2fr]">
            <div className="card p-6">
                <h3 className="mb-5 font-serif text-xl font-semibold">
                    Lead profile
                </h3>
                <div className="grid grid-cols-2 gap-6">
                    <Info label="Priority" value={d.priority} />
                    <Info label="Assigned to" value={d.assignee?.name} />
                    <Info label="Email" value={d.email} />
                    <Info label="Occasion" value={d.occasion} />
                    <Info
                        label="Budget"
                        value={
                            d.budget_min
                                ? `${money(d.budget_min)} – ${money(d.budget_max)}`
                                : null
                        }
                    />
                    <Info
                        label="Interests"
                        value={interests.length ? interests.join(", ") : null}
                    />
                </div>
                {profileNotes && (
                    <p className="mt-6 border-t border-[#eee8dc] pt-5 text-sm leading-6 text-[#6e675b]">
                        {profileNotes}
                    </p>
                )}
            </div>
            <Timeline items={timeline} />
        </div>
    );
}
function normalizeList(value) {
    if (Array.isArray(value)) return value;
    if (typeof value !== "string" || !value.trim()) return [];

    try {
        const parsed = JSON.parse(value);
        if (Array.isArray(parsed)) return parsed;
    } catch {
        // Legacy records may contain a plain comma-separated value.
    }

    return value
        .split(",")
        .map((item) => item.trim())
        .filter(Boolean);
}
function Timeline({ items }) {
    return (
        <div className="card p-6">
            <h3 className="mb-5 font-serif text-xl font-semibold">
                Relationship timeline
            </h3>
            {items.length ? (
                items.map((x, i) => (
                    <div
                        key={`${x.kind}-${x.id}`}
                        className="relative flex gap-4 pb-6 last:pb-0"
                    >
                        <div className="relative z-10 mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#f1e8d4] text-[#a67d2f]">
                            <MessageSquare size={14} />
                        </div>
                        {i < items.length - 1 && (
                            <i className="absolute left-[15px] top-9 h-[calc(100%-30px)] w-px bg-[#e5dece]" />
                        )}
                        <div>
                            <div className="flex items-center gap-2">
                                <b className="text-sm">{x.kind}</b>
                                <span className="text-xs text-[#9a9285]">
                                    {new Date(
                                        x.date || x.created_at,
                                    ).toLocaleString("en-IN")}
                                </span>
                            </div>
                            <p className="mt-1 text-sm leading-6 text-[#746c60]">
                                {x.text || "Status updated"}
                            </p>
                        </div>
                    </div>
                ))
            ) : (
                <p className="text-sm text-[#8d8578]">No activity yet.</p>
            )}
        </div>
    );
}
function CustomerView({ d }) {
    const companyName = useCompanyName();
    return (
        <>
            <div className="grid gap-4 md:grid-cols-4">
                <div className="card p-5">
                    <Info
                        label="Lifetime value"
                        value={money(d.lifetime_value)}
                    />
                </div>
                <div className="card p-5">
                    <Info
                        label="Loyalty balance"
                        value={`${d.loyalty_balance || 0} points`}
                    />
                </div>
                <div className="card p-5">
                    <Info label="Purchases" value={d.sales?.length} />
                </div>
                <div className="card p-5">
                    <Info
                        label="Custom orders"
                        value={d.custom_orders?.length}
                    />
                </div>
            </div>
            {d.privilege_cards?.length > 0 && (
                <div className="mt-5">
                    <h3 className="mb-3 font-serif text-xl font-semibold">
                        Privilege membership
                    </h3>
                    <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        {d.privilege_cards.map((card) => (
                            <PrivilegeCardVisual
                                key={card.id}
                                card={{ ...card, customer: d }}
                                companyName={companyName}
                                compact
                            />
                        ))}
                    </div>
                </div>
            )}
            <div className="mt-5 grid gap-5 xl:grid-cols-2">
                <div className="card overflow-hidden">
                    <h3 className="border-b border-[#eee8dc] px-6 py-5 font-serif text-xl font-semibold">
                        Purchase history
                    </h3>
                    {d.sales?.map((x) => (
                        <div
                            key={x.id}
                            className="flex items-center justify-between border-b border-[#f0ece4] px-6 py-4 last:border-0"
                        >
                            <div>
                                <b className="text-sm">{x.invoice_number}</b>
                                <p className="text-xs text-[#8e8679]">
                                    {new Date(x.sale_date).toLocaleDateString(
                                        "en-IN",
                                    )}{" "}
                                    · {x.items?.[0]?.jewellery_type}
                                </p>
                            </div>
                            <div className="text-right">
                                <b>{money(x.final_amount)}</b>
                                <br />
                                <Badge>{x.payment_status}</Badge>
                            </div>
                        </div>
                    ))}
                </div>
                <div className="card p-6">
                    <h3 className="mb-5 font-serif text-xl font-semibold">
                        Personal & family details
                    </h3>
                    <div className="grid grid-cols-2 gap-6">
                        <Info label="Email" value={d.email} />
                        <Info label="City" value={d.city} />
                        <Info label="Birthday" value={d.birthday} />
                        <Info label="Anniversary" value={d.anniversary} />
                    </div>
                    <div className="mt-6 border-t border-[#eee8dc] pt-5">
                        {d.family_members?.map((x) => (
                            <div
                                key={x.id}
                                className="mb-3 flex items-center gap-3"
                            >
                                <UserRound
                                    size={16}
                                    className="text-[#b58b36]"
                                />
                                <span className="text-sm">
                                    <b>{x.name}</b> · {x.relation}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </>
    );
}
function OrderView({ d, onStatus }) {
    const statuses = ["Processing", "Cancelled", "Order Ready"];
    return (
        <div className="grid gap-5 xl:grid-cols-[.8fr_1.2fr]">
            <div className="card p-6">
                <h3 className="mb-5 font-serif text-xl font-semibold">
                    Order specification
                </h3>
                <div className="grid grid-cols-2 gap-6">
                    <Info label="Customer" value={d.customer?.name} />
                    <Info label="Due date" value={d.due_date} />
                    <Info label="Estimated" value={money(d.estimated_amount)} />
                    <Info label="Advance" value={money(d.advance_payment)} />
                    <Info
                        label="Weight"
                        value={d.approx_weight && `${d.approx_weight} g`}
                    />
                    <Info label="Karigar" value={d.vendor_name} />
                    <Info label="Approval" value={d.approval_status} />
                    <Info label="Owner" value={d.assignee?.name} />
                </div>
                <label className="mt-6 block">
                    <span className="label">Update order status</span>
                    <select
                        className="field"
                        value={d.status}
                        onChange={(e) => onStatus(e.target.value)}
                    >
                        {statuses.map((x) => (
                            <option key={x}>{x}</option>
                        ))}
                    </select>
                </label>
            </div>
            <Timeline
                items={(d.status_logs || []).map((x) => ({
                    ...x,
                    kind: x.to_status,
                    text:
                        x.note ||
                        `Order moved from ${x.from_status || "creation"} to ${x.to_status}`,
                }))}
            />
        </div>
    );
}
function EventView({ d }) {
    const [roi, setRoi] = useState();
    useEffect(() => {
        api.get(`/exhibitions/${d.id}/roi`).then((r) => setRoi(r.data));
    }, [d.id]);
    return (
        <>
            <div className="grid gap-4 md:grid-cols-4">
                <div className="card p-5">
                    <Info
                        label="Leads captured"
                        value={roi?.leads || d.leads?.length}
                    />
                </div>
                <div className="card p-5">
                    <Info label="Event expense" value={money(roi?.expense)} />
                </div>
                <div className="card p-5">
                    <Info
                        label="Converted revenue"
                        value={money(roi?.revenue)}
                    />
                </div>
                <div className="card p-5">
                    <Info label="Event ROI" value={`${roi?.roi || 0}%`} />
                </div>
            </div>
            <div className="card mt-5 overflow-hidden">
                <h3 className="border-b border-[#eee8dc] px-6 py-5 font-serif text-xl font-semibold">
                    Event lead book
                </h3>
                {d.leads?.map((x) => (
                    <div
                        className="flex items-center justify-between border-b border-[#f0ece4] px-6 py-4"
                        key={x.id}
                    >
                        <div>
                            <b className="text-sm">{x.name}</b>
                            <p className="text-xs text-[#8e8679]">
                                {x.mobile} · {x.product_interests?.join(", ")}
                            </p>
                        </div>
                        <Badge>{x.status}</Badge>
                    </div>
                ))}
            </div>
        </>
    );
}
