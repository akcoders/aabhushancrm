import { useState } from "react";
import { Navigate } from "react-router-dom";
import { ArrowRight, ShieldCheck } from "lucide-react";
import { useAuth } from "../AuthContext";
import { ErrorText } from "../components/UI";
export default function Login() {
    const { user, login } = useAuth();
    const [form, setForm] = useState({
        email: "admin@jewellerycrm.test",
        password: "Password@123",
    });
    const [error, setError] = useState();
    const [busy, setBusy] = useState(false);
    if (user) return <Navigate to="/" />;
    const submit = async (e) => {
        e.preventDefault();
        setBusy(true);
        setError(null);
        try {
            await login(form.email, form.password);
        } catch (x) {
            setError(x);
        } finally {
            setBusy(false);
        }
    };
    return (
        <div className="grid min-h-screen bg-[#f5f1e8] lg:grid-cols-[1.05fr_.95fr]">
            <section className="relative hidden overflow-hidden bg-[#080808] p-14 text-white lg:flex lg:flex-col lg:justify-between">
                <div className="absolute -right-32 top-16 h-96 w-96 rounded-full border border-[#d8b867]/20" />
                <div className="absolute -right-16 top-32 h-72 w-72 rounded-full border border-[#d8b867]/20" />
                <img
                    src="/crm/kalasha-logo.png"
                    alt="Kalasha Fine Jewels"
                    className="relative h-auto w-64 object-contain"
                />
                <div className="relative max-w-xl">
                    <p className="mb-5 text-xs font-semibold uppercase tracking-[.3em] text-[#c9a458]">
                        Relationships, refined
                    </p>
                    <h1 className="font-serif text-6xl leading-[1.06]">
                        Every customer story, beautifully remembered.
                    </h1>
                    <p className="mt-7 max-w-md text-base leading-7 text-white/55">
                        A focused workspace for your showroom, from first
                        enquiry to lifelong patronage.
                    </p>
                </div>
                <div className="relative flex gap-7 text-sm text-white/45">
                    <span className="flex items-center gap-2">
                        <ShieldCheck size={17} />
                        Secure & role-based
                    </span>
                    <span>Built for fine jewellery teams</span>
                </div>
            </section>
            <section className="flex items-center justify-center p-6">
                <div className="w-full max-w-md">
                    <img
                        src="/crm/kalasha-logo.png"
                        alt="Kalasha Fine Jewels"
                        className="mb-8 h-auto w-52 object-contain lg:hidden"
                    />
                    <p className="text-xs font-semibold uppercase tracking-[.2em] text-[#b58b36]">
                        Welcome back
                    </p>
                    <h2 className="mt-2 font-serif text-4xl font-semibold">
                        Sign in to your showroom
                    </h2>
                    <p className="mb-8 mt-3 text-sm text-[#7e7668]">
                        Use the seeded administrator account or your staff
                        credentials.
                    </p>
                    <ErrorText error={error} />
                    <form onSubmit={submit} className="space-y-5">
                        <label>
                            <span className="label">Email address</span>
                            <input
                                className="field py-3"
                                type="email"
                                value={form.email}
                                onChange={(e) =>
                                    setForm({ ...form, email: e.target.value })
                                }
                            />
                        </label>
                        <label>
                            <span className="label">Password</span>
                            <input
                                className="field py-3"
                                type="password"
                                value={form.password}
                                onChange={(e) =>
                                    setForm({
                                        ...form,
                                        password: e.target.value,
                                    })
                                }
                            />
                        </label>
                        <button
                            disabled={busy}
                            className="btn-primary w-full py-3.5"
                        >
                            {busy ? "Signing in…" : "Enter Jewellery CRM"}
                            <ArrowRight size={17} />
                        </button>
                    </form>
                    <div className="mt-7 rounded-xl border border-[#e3dccd] bg-white/60 p-4 text-xs leading-5 text-[#71695b]">
                        <strong>Demo:</strong> admin@jewellerycrm.test
                        <br />
                        Password@123
                    </div>
                </div>
            </section>
        </div>
    );
}
