import { useEffect, useMemo, useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { ArrowLeft, Plus, Trash2 } from "lucide-react";
import api from "../api";
import { ErrorText } from "../components/UI";
import { money } from "../config";
import SearchableSelect from "../components/SearchableSelect";
import { useAuth } from "../AuthContext";
const blank = {
    product_category: "Gold",
    jewellery_type: "Ring",
    metal_type: "Gold",
    purity: "22K",
    gross_weight: "",
    net_weight: "",
    stone_weight: "",
    making_charge: 0,
    discount: 0,
    tax: 0,
    total: "",
};
export default function SaleForm() {
    const nav = useNavigate();
    const { user, hasPermission } = useAuth();
    const [customers, setCustomers] = useState([]),
        [staff, setStaff] = useState(user ? [user] : []),
        [form, setForm] = useState({
            customer_id: "",
            sale_date: new Date().toISOString().slice(0, 10),
            staff_id: user?.id || "",
            discount: 0,
            tax: 0,
            notes: "",
            items: [{ ...blank }],
            payments: [{ mode: "UPI", amount: "", reference: "" }],
        }),
        [error, setError] = useState(),
        [busy, setBusy] = useState(false);
    useEffect(() => {
        api.get("/customers", { params: { per_page: 100 } }).then((r) =>
            setCustomers(r.data.data),
        );
        if (hasPermission("staff.view")) {
            api.get("/staff", { params: { per_page: 100 } }).then((r) =>
                setStaff(r.data.data),
            );
        }
    }, [hasPermission, user]);
    const subtotal = useMemo(
        () => form.items.reduce((s, x) => s + Number(x.total || 0), 0),
        [form.items],
    );
    const final = subtotal - Number(form.discount || 0) + Number(form.tax || 0);
    const item = (i, k, v) =>
        setForm({
            ...form,
            items: form.items.map((x, n) => (n === i ? { ...x, [k]: v } : x)),
        });
    const submit = async (e) => {
        e.preventDefault();
        setBusy(true);
        setError();
        try {
            await api.post("/sales", {
                ...form,
                subtotal,
                final_amount: final,
                payments: form.payments.filter((x) => x.amount),
            });
            nav("/sales");
        } catch (x) {
            setError(x);
        } finally {
            setBusy(false);
        }
    };
    return (
        <div>
            <Link
                to="/sales"
                className="mb-5 inline-flex items-center gap-2 text-sm text-[#80786a]"
            >
                <ArrowLeft size={17} />
                Back to sales
            </Link>
            <div className="mb-6">
                <p className="text-xs font-semibold uppercase tracking-[.16em] text-[#aa8131]">
                    New transaction
                </p>
                <h2 className="page-title">Create sale entry</h2>
            </div>
            <form onSubmit={submit}>
                <ErrorText error={error} />
                <div className="card mb-5 grid gap-4 p-6 md:grid-cols-3">
                    <label>
                        <span className="label">Customer *</span>
                        <SearchableSelect
                            required
                            value={form.customer_id}
                            options={customers.map((customer) => ({
                                value: customer.id,
                                label: `${customer.name} · ${customer.mobile || "No mobile"}`,
                                search: `${customer.email || ""} ${customer.customer_code || ""}`,
                            }))}
                            placeholder="Search customer name, mobile or email..."
                            onChange={(customerId) =>
                                setForm({
                                    ...form,
                                    customer_id: customerId,
                                })
                            }
                        />
                    </label>
                    <label>
                        <span className="label">Sale date *</span>
                        <input
                            required
                            type="date"
                            className="field"
                            value={form.sale_date}
                            onChange={(e) =>
                                setForm({ ...form, sale_date: e.target.value })
                            }
                        />
                    </label>
                    <label>
                        <span className="label">Sales executive</span>
                        <SearchableSelect
                            value={form.staff_id}
                            options={staff.map((person) => ({
                                value: person.id,
                                label: `${person.name} · ${person.email || person.phone || "Staff"}`,
                            }))}
                            placeholder="Search sales executive..."
                            onChange={(staffId) =>
                                setForm({ ...form, staff_id: staffId })
                            }
                        />
                    </label>
                </div>
                <div className="card mb-5 overflow-hidden">
                    <div className="flex items-center justify-between border-b border-[#eee8dc] px-6 py-4">
                        <h3 className="font-serif text-xl font-semibold">
                            Jewellery items
                        </h3>
                        <button
                            type="button"
                            className="btn-secondary py-2"
                            onClick={() =>
                                setForm({
                                    ...form,
                                    items: [...form.items, { ...blank }],
                                })
                            }
                        >
                            <Plus size={15} />
                            Add item
                        </button>
                    </div>
                    {form.items.map((x, i) => (
                        <div
                            key={i}
                            className="grid gap-3 border-b border-[#eee8dc] p-5 md:grid-cols-4"
                        >
                            <label>
                                <span className="label">Category</span>
                                <select
                                    className="field"
                                    value={x.product_category}
                                    onChange={(e) =>
                                        item(
                                            i,
                                            "product_category",
                                            e.target.value,
                                        )
                                    }
                                >
                                    {[
                                        "Gold",
                                        "Diamond",
                                        "Bridal",
                                        "Silver",
                                        "Polki",
                                        "Kundan",
                                    ].map((v) => (
                                        <option key={v}>{v}</option>
                                    ))}
                                </select>
                            </label>
                            <label>
                                <span className="label">Jewellery type</span>
                                <input
                                    className="field"
                                    value={x.jewellery_type}
                                    onChange={(e) =>
                                        item(
                                            i,
                                            "jewellery_type",
                                            e.target.value,
                                        )
                                    }
                                />
                            </label>
                            <label>
                                <span className="label">Metal</span>
                                <select
                                    className="field"
                                    value={x.metal_type}
                                    onChange={(e) =>
                                        item(i, "metal_type", e.target.value)
                                    }
                                >
                                    {[
                                        "Gold",
                                        "Rose Gold",
                                        "Platinum",
                                        "Silver",
                                    ].map((v) => (
                                        <option key={v}>{v}</option>
                                    ))}
                                </select>
                            </label>
                            <label>
                                <span className="label">Purity</span>
                                <input
                                    className="field"
                                    value={x.purity}
                                    onChange={(e) =>
                                        item(i, "purity", e.target.value)
                                    }
                                />
                            </label>
                            {[
                                ["gross_weight", "Gross wt."],
                                ["net_weight", "Net wt."],
                                ["stone_weight", "Stone wt."],
                                ["making_charge", "Making charge"],
                                ["wastage", "Wastage %"],
                                ["discount", "Item discount"],
                                ["tax", "Tax"],
                                ["total", "Item total *"],
                            ].map(([k, l]) => (
                                <label key={k}>
                                    <span className="label">{l}</span>
                                    <input
                                        type="number"
                                        step=".001"
                                        required={k === "total"}
                                        className="field"
                                        value={x[k] ?? ""}
                                        onChange={(e) =>
                                            item(i, k, e.target.value)
                                        }
                                    />
                                </label>
                            ))}
                            {form.items.length > 1 && (
                                <button
                                    type="button"
                                    onClick={() =>
                                        setForm({
                                            ...form,
                                            items: form.items.filter(
                                                (_, n) => n !== i,
                                            ),
                                        })
                                    }
                                    className="self-end justify-self-start rounded-lg p-2 text-red-500"
                                >
                                    <Trash2 size={17} />
                                </button>
                            )}
                        </div>
                    ))}
                </div>
                <div className="grid gap-5 lg:grid-cols-2">
                    <div className="card p-6">
                        <h3 className="mb-4 font-serif text-xl font-semibold">
                            Payment
                        </h3>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <label>
                                <span className="label">Mode</span>
                                <select
                                    className="field"
                                    value={form.payments[0].mode}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            payments: [
                                                {
                                                    ...form.payments[0],
                                                    mode: e.target.value,
                                                },
                                            ],
                                        })
                                    }
                                >
                                    {[
                                        "Cash",
                                        "UPI",
                                        "Card",
                                        "Bank Transfer",
                                        "Cheque",
                                    ].map((x) => (
                                        <option key={x}>{x}</option>
                                    ))}
                                </select>
                            </label>
                            <label>
                                <span className="label">Amount</span>
                                <input
                                    type="number"
                                    className="field"
                                    value={form.payments[0].amount}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            payments: [
                                                {
                                                    ...form.payments[0],
                                                    amount: e.target.value,
                                                },
                                            ],
                                        })
                                    }
                                />
                            </label>
                        </div>
                    </div>
                    <div className="card p-6">
                        <div className="space-y-3 text-sm">
                            <div className="flex justify-between text-[#777064]">
                                <span>Subtotal</span>
                                <span>{money(subtotal)}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-[#777064]">Discount</span>
                                <input
                                    type="number"
                                    className="field w-32 py-1.5 text-right"
                                    value={form.discount}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            discount: e.target.value,
                                        })
                                    }
                                />
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-[#777064]">Tax</span>
                                <input
                                    type="number"
                                    className="field w-32 py-1.5 text-right"
                                    value={form.tax}
                                    onChange={(e) =>
                                        setForm({
                                            ...form,
                                            tax: e.target.value,
                                        })
                                    }
                                />
                            </div>
                            <div className="flex justify-between border-t border-[#e9e2d5] pt-4 text-lg font-bold">
                                <span>Final amount</span>
                                <span>{money(final)}</span>
                            </div>
                        </div>
                        <button
                            disabled={busy}
                            className="btn-primary mt-6 w-full py-3"
                        >
                            {busy ? "Creating invoice…" : "Complete sale"}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    );
}
