import { useState } from "react";
import { useParams } from "react-router-dom";
import { CheckCircle2, Diamond, Sparkles } from "lucide-react";
import api from "../api";
import { ErrorText } from "../components/UI";
const interests = [
    "Bridal jewellery",
    "Gold jewellery",
    "Diamond jewellery",
    "Silver jewellery",
    "Polki",
    "Kundan",
    "Custom design",
];
export default function EventCapture() {
    const { token } = useParams();
    const [form, setForm] = useState({
            name: "",
            mobile: "",
            email: "",
            product_interests: [],
            budget_min: "",
            budget_max: "",
            visit_notes: "",
            whatsapp_opt_in: true,
            email_opt_in: true,
        }),
        [result, setResult] = useState(),
        [error, setError] = useState();
    const toggle = (x) =>
        setForm({
            ...form,
            product_interests: form.product_interests.includes(x)
                ? form.product_interests.filter((y) => y !== x)
                : [...form.product_interests, x],
        });
    const submit = async (e) => {
        e.preventDefault();
        setError();
        try {
            const { data } = await api.post(`/events/${token}/capture`, form);
            setResult(data);
        } catch (x) {
            setError(x);
        }
    };
    return (
        <div className="min-h-screen bg-[#f4efe5] p-5 md:p-10">
            <div className="mx-auto max-w-2xl">
                <div className="mb-8 text-center">
                    <img
                        src="/crm/kalasha-logo.png"
                        alt="Kalasha Fine Jewels"
                        className="mx-auto mb-5 h-auto w-52 object-contain"
                    />
                    <h1 className="mt-2 font-serif text-4xl font-semibold">
                        Discover your next heirloom
                    </h1>
                    <p className="mx-auto mt-3 max-w-lg text-sm leading-6 text-[#777064]">
                        Tell us what catches your eye. If we have met before, we
                        will quietly reconnect your preferences so you never
                        need to start again.
                    </p>
                </div>
                <div className="card p-6 md:p-9">
                    {result ? (
                        <div className="py-10 text-center">
                            {result.recognized ? (
                                <Diamond
                                    className="mx-auto mb-4 text-[#b58b36]"
                                    fill="currentColor"
                                    size={50}
                                />
                            ) : (
                                <CheckCircle2
                                    className="mx-auto mb-4 text-emerald-600"
                                    size={48}
                                />
                            )}
                            <p className="text-xs font-semibold uppercase tracking-widest text-[#a47a2a]">
                                {result.visitor_type}
                            </p>
                            <h2 className="mt-2 font-serif text-3xl font-semibold">
                                {result.recognized
                                    ? "Welcome back"
                                    : "Thank you"}
                            </h2>
                            <p className="mx-auto mt-3 max-w-md text-sm leading-6 text-[#777064]">
                                {result.message}
                            </p>
                            {result.recognized && (
                                <div className="mx-auto mt-5 max-w-md rounded-xl bg-[#f8f3e8] p-4 text-xs leading-5 text-[#746d61]">
                                    Your past visits and purchases are visible
                                    only to authorized showroom staff, helping
                                    us serve you consistently and respectfully.
                                </div>
                            )}
                        </div>
                    ) : (
                        <form onSubmit={submit}>
                            <ErrorText error={error} />
                            <div className="grid gap-5 md:grid-cols-2">
                                <label>
                                    <span className="label">Your name *</span>
                                    <input
                                        required
                                        className="field"
                                        value={form.name}
                                        onChange={(e) =>
                                            setForm({
                                                ...form,
                                                name: e.target.value,
                                            })
                                        }
                                    />
                                </label>
                                <label>
                                    <span className="label">
                                        Mobile number *
                                    </span>
                                    <input
                                        required
                                        className="field"
                                        value={form.mobile}
                                        onChange={(e) =>
                                            setForm({
                                                ...form,
                                                mobile: e.target.value,
                                            })
                                        }
                                    />
                                </label>
                                <label className="md:col-span-2">
                                    <span className="label">Email address</span>
                                    <input
                                        type="email"
                                        className="field"
                                        value={form.email}
                                        onChange={(e) =>
                                            setForm({
                                                ...form,
                                                email: e.target.value,
                                            })
                                        }
                                    />
                                </label>
                                <fieldset className="md:col-span-2">
                                    <legend className="label">
                                        I am interested in
                                    </legend>
                                    <div className="flex flex-wrap gap-2">
                                        {interests.map((x) => (
                                            <button
                                                type="button"
                                                onClick={() => toggle(x)}
                                                key={x}
                                                className={`rounded-full border px-3.5 py-2 text-xs font-semibold ${form.product_interests.includes(x) ? "border-[#b58b36] bg-[#f5ecd8] text-[#8e6824]" : "border-[#ddd5c7] bg-white text-[#756d61]"}`}
                                            >
                                                {x}
                                            </button>
                                        ))}
                                    </div>
                                </fieldset>
                                <label>
                                    <span className="label">Budget from</span>
                                    <input
                                        type="number"
                                        className="field"
                                        value={form.budget_min}
                                        onChange={(e) =>
                                            setForm({
                                                ...form,
                                                budget_min: e.target.value,
                                            })
                                        }
                                    />
                                </label>
                                <label>
                                    <span className="label">Budget up to</span>
                                    <input
                                        type="number"
                                        className="field"
                                        value={form.budget_max}
                                        onChange={(e) =>
                                            setForm({
                                                ...form,
                                                budget_max: e.target.value,
                                            })
                                        }
                                    />
                                </label>
                                <label className="md:col-span-2">
                                    <span className="label">
                                        Anything you would like us to remember?
                                    </span>
                                    <textarea
                                        className="field"
                                        rows="3"
                                        value={form.visit_notes}
                                        onChange={(e) =>
                                            setForm({
                                                ...form,
                                                visit_notes: e.target.value,
                                            })
                                        }
                                        placeholder="Design, occasion, timeline, or a piece you liked..."
                                    />
                                </label>
                                <div className="md:col-span-2 rounded-xl border border-[#e5dece] bg-[#fbf9f5] p-4">
                                    <p className="mb-3 text-xs font-semibold">
                                        Communication preferences
                                    </p>
                                    <label className="mb-2 flex items-start gap-2 text-xs text-[#716a5e]">
                                        <input
                                            type="checkbox"
                                            className="mt-0.5 accent-[#b58b36]"
                                            checked={form.whatsapp_opt_in}
                                            onChange={(e) =>
                                                setForm({
                                                    ...form,
                                                    whatsapp_opt_in:
                                                        e.target.checked,
                                                })
                                            }
                                        />
                                        <span>
                                            I agree to receive relevant
                                            appointment, jewellery care and
                                            offer messages on WhatsApp. I can
                                            opt out anytime.
                                        </span>
                                    </label>
                                    <label className="flex items-start gap-2 text-xs text-[#716a5e]">
                                        <input
                                            type="checkbox"
                                            className="mt-0.5 accent-[#b58b36]"
                                            checked={form.email_opt_in}
                                            onChange={(e) =>
                                                setForm({
                                                    ...form,
                                                    email_opt_in:
                                                        e.target.checked,
                                                })
                                            }
                                        />
                                        <span>
                                            I agree to receive relevant email
                                            updates and private preview
                                            invitations.
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <button className="btn-primary mt-7 w-full py-3.5">
                                <Sparkles size={17} />
                                Save my preferences
                            </button>
                            <p className="mt-4 text-center text-[10px] leading-4 text-[#a0998d]">
                                Your details are used to provide a continuous,
                                personalized jewellery service. Your consent
                                choices are respected.
                            </p>
                        </form>
                    )}
                </div>
            </div>
        </div>
    );
}
