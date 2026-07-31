import { useEffect, useState } from "react";
import { Copy, Plus, Video } from "lucide-react";
import api from "../api";
import { Badge, ErrorText, Loading, Modal } from "../components/UI";
import SearchableSelect from "../components/SearchableSelect";
export default function VideoSales() {
    const [items, setItems] = useState(),
        [contacts, setContacts] = useState([]),
        [show, setShow] = useState(false),
        [form, setForm] = useState({
            title: "Private jewellery consultation",
            scheduled_at: "",
            customer_id: "",
            lead_id: "",
            notes: "",
        }),
        [error, setError] = useState();
    const load = () =>
        api.get("/video-call-sales").then((r) => setItems(r.data.data));
    useEffect(() => {
        load();
        Promise.all([
            api.get("/customers", { params: { per_page: 100 } }),
            api.get("/leads", { params: { per_page: 100 } }),
        ]).then(([customers, leads]) =>
            setContacts([
                ...customers.data.data.map((person) => ({
                    value: `customer:${person.id}`,
                    label: `${person.name} · ${person.mobile || "No mobile"} · Customer`,
                    search: `${person.email || ""} ${person.customer_code || ""}`,
                })),
                ...leads.data.data.map((person) => ({
                    value: `lead:${person.id}`,
                    label: `${person.name} · ${person.mobile || "No mobile"} · Lead`,
                    search: `${person.email || ""} ${person.status || ""}`,
                })),
            ]),
        );
    }, []);
    const save = async () => {
        try {
            await api.post("/video-call-sales", {
                ...form,
                customer_id: form.customer_id || null,
                lead_id: form.lead_id || null,
            });
            setShow(false);
            load();
        } catch (e) {
            setError(e);
        }
    };
    if (!items) return <Loading />;
    return (
        <div>
            <div className="mb-6 flex items-end justify-between">
                <div>
                    <p className="text-xs font-bold uppercase tracking-[.16em] text-[#aa8131]">
                        Remote assisted selling
                    </p>
                    <h2 className="page-title">Video Call Sales</h2>
                    <p className="text-sm text-[#857d70]">
                        Schedule private consultations, present jewellery live
                        and record the sale outcome.
                    </p>
                </div>
                <button className="btn-primary" onClick={() => setShow(true)}>
                    <Plus size={16} />
                    Schedule video call
                </button>
            </div>
            <div className="card overflow-x-auto">
                <table className="w-full min-w-[800px] text-left">
                    <thead>
                        <tr className="bg-[#faf8f3] text-xs uppercase text-[#81796d]">
                            <th className="p-4">Consultation</th>
                            <th>Customer / lead</th>
                            <th>Executive</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {items.map((x) => (
                            <tr
                                className="border-t border-[#eee8dc]"
                                key={x.id}
                            >
                                <td className="p-4">
                                    <b className="text-sm">{x.title}</b>
                                    <p className="text-xs text-[#81796d]">
                                        {x.outcome || "Outcome pending"}
                                    </p>
                                </td>
                                <td className="text-sm">
                                    {x.customer?.name ||
                                        x.lead?.name ||
                                        "Guest customer"}
                                </td>
                                <td className="text-sm">{x.staff?.name}</td>
                                <td className="text-sm">
                                    {new Date(x.scheduled_at).toLocaleString(
                                        "en-IN",
                                    )}
                                </td>
                                <td>
                                    <Badge>{x.status}</Badge>
                                </td>
                                <td>
                                    <div className="flex gap-2">
                                        <button
                                            className="btn-secondary py-1.5"
                                            onClick={() =>
                                                navigator.clipboard.writeText(
                                                    location.origin +
                                                        `/video-sales/${x.id}/room`,
                                                )
                                            }
                                        >
                                            <Copy size={13} />
                                        </button>
                                        <a
                                            className="btn-primary py-1.5"
                                            href={`/video-sales/${x.id}/room`}
                                        >
                                            <Video size={13} />
                                            Join
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            {show && (
                <Modal
                    title="Schedule video sale"
                    onClose={() => setShow(false)}
                >
                    <ErrorText error={error} />
                    <div className="space-y-3">
                        <label>
                            <span className="label">Title</span>
                            <input
                                className="field"
                                value={form.title}
                                onChange={(e) =>
                                    setForm({ ...form, title: e.target.value })
                                }
                            />
                        </label>
                        <label>
                            <span className="label">Scheduled at</span>
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
                        </label>
                        <label>
                            <span className="label">Customer or lead</span>
                            <SearchableSelect
                                value={
                                    form.customer_id
                                        ? `customer:${form.customer_id}`
                                        : form.lead_id
                                          ? `lead:${form.lead_id}`
                                          : ""
                                }
                                options={contacts}
                                placeholder="Search name, mobile, email or customer code..."
                                onChange={(selected) => {
                                    const [type, id] = selected
                                        ? String(selected).split(":")
                                        : ["", ""];
                                    setForm({
                                        ...form,
                                        customer_id:
                                            type === "customer" ? id : "",
                                        lead_id: type === "lead" ? id : "",
                                    });
                                }}
                            />
                            <p className="mt-1 text-[10px] text-[#91897c]">
                                Search by name, mobile, email or customer code. No database ID is required.
                            </p>
                        </label>
                        <label>
                            <span className="label">Preparation notes</span>
                            <textarea
                                className="field"
                                value={form.notes}
                                onChange={(e) =>
                                    setForm({ ...form, notes: e.target.value })
                                }
                            />
                        </label>
                        <button className="btn-primary w-full" onClick={save}>
                            Create private Jitsi room
                        </button>
                    </div>
                </Modal>
            )}
        </div>
    );
}
