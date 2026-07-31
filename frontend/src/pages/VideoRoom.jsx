import { useEffect, useRef, useState } from "react";
import { ArrowLeft, Check, Copy, ExternalLink, PhoneOff, Video } from "lucide-react";
import { useNavigate, useParams } from "react-router-dom";
import api from "../api";
import { Loading } from "../components/UI";
import { useAuth } from "../AuthContext";

export default function VideoRoom() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { user } = useAuth();
    const host = useRef();
    const instance = useRef();
    const [call, setCall] = useState();
    const [error, setError] = useState();
    const [joined, setJoined] = useState(false);
    const [copied, setCopied] = useState(false);

    useEffect(() => {
        let disposed = false;
        Promise.all([
            api.get(`/video-call-sales/${id}`),
            api.get("/video-call-sales-config"),
        ])
            .then(([callResponse, configResponse]) => {
                if (disposed) return;
                const consultation = callResponse.data;
                const domain = configResponse.data.domain;
                setCall(consultation);

                const startMeeting = () => {
                    if (disposed || !host.current) return;
                    instance.current = new window.JitsiMeetExternalAPI(domain, {
                        roomName: consultation.room_name,
                        parentNode: host.current,
                        width: "100%",
                        height: "100%",
                        userInfo: {
                            displayName: user?.name || "Kalasha consultant",
                            email: user?.email,
                        },
                        configOverwrite: {
                            prejoinPageEnabled: false,
                            disableDeepLinking: true,
                            startWithAudioMuted: false,
                            startWithVideoMuted: false,
                            enableWelcomePage: false,
                            toolbarButtons: [
                                "microphone",
                                "camera",
                                "desktop",
                                "chat",
                                "participants-pane",
                                "tileview",
                                "fullscreen",
                                "settings",
                                "hangup",
                            ],
                        },
                        interfaceConfigOverwrite: {
                            APP_NAME: "Kalasha Video Consultation",
                            MOBILE_APP_PROMO: false,
                            SHOW_JITSI_WATERMARK: false,
                            SHOW_WATERMARK_FOR_GUESTS: false,
                        },
                    });
                    instance.current.addListener("videoConferenceJoined", () => {
                        setJoined(true);
                        api.put(`/video-call-sales/${id}`, { status: "In Progress" });
                    });
                    instance.current.addListener("videoConferenceLeft", () => {
                        setJoined(false);
                        api.put(`/video-call-sales/${id}`, { status: "Completed" });
                    });
                };

                if (window.JitsiMeetExternalAPI) startMeeting();
                else {
                    const script = document.createElement("script");
                    script.src = `https://${domain}/external_api.js`;
                    script.async = true;
                    script.onload = startMeeting;
                    script.onerror = () => setError("Unable to load Jitsi Meet.");
                    document.body.appendChild(script);
                }
            })
            .catch((requestError) =>
                setError(requestError.response?.data?.message || "Unable to open meeting"),
            );
        return () => {
            disposed = true;
            instance.current?.dispose();
        };
    }, [id, user?.email, user?.name]);

    const copyInvitation = async () => {
        await navigator.clipboard.writeText(call.meeting_url);
        setCopied(true);
        window.setTimeout(() => setCopied(false), 1800);
    };
    const leave = async () => {
        instance.current?.executeCommand("hangup");
        await api.put(`/video-call-sales/${id}`, { status: "Completed" }).catch(() => {});
        navigate("/video-sales");
    };

    return (
        <div className="fixed inset-0 z-[100] flex flex-col bg-[#111] text-white">
            <header className="flex min-h-20 flex-wrap items-center justify-between gap-3 border-b border-white/10 bg-[#171612] px-4 py-3 md:px-6">
                <div className="flex min-w-0 items-center gap-3">
                    <button
                        onClick={() => navigate("/video-sales")}
                        className="rounded-full border border-white/15 p-2.5 hover:bg-white/10"
                        title="Back to video sales"
                    >
                        <ArrowLeft size={18} />
                    </button>
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#b58b36]">
                        <Video size={20} />
                    </div>
                    <div className="min-w-0">
                        <h1 className="truncate font-serif text-xl font-semibold md:text-2xl">
                            {call?.title || "Private video consultation"}
                        </h1>
                        <p className="truncate text-xs text-white/50">
                            {call
                                ? `${call.customer?.name || call.lead?.name || "Guest customer"} · ${call.staff?.name || "Kalasha consultant"}`
                                : "Preparing secure meeting..."}
                        </p>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <span className="hidden items-center gap-2 rounded-full bg-white/5 px-3 py-2 text-xs text-white/60 sm:flex">
                        <i
                            className={`h-2 w-2 rounded-full ${joined ? "bg-emerald-400" : "bg-amber-400"}`}
                        />
                        {joined ? "Consultation live" : "Connecting"}
                    </span>
                    <button
                        onClick={copyInvitation}
                        disabled={!call}
                        className="inline-flex items-center gap-2 rounded-xl border border-white/15 px-3 py-2.5 text-sm hover:bg-white/10"
                    >
                        {copied ? <Check size={16} /> : <Copy size={16} />}
                        <span className="hidden sm:inline">{copied ? "Copied" : "Copy invite"}</span>
                    </button>
                    {call && (
                        <a
                            href={call.meeting_url}
                            target="_blank"
                            rel="noreferrer"
                            className="rounded-xl border border-white/15 p-2.5 hover:bg-white/10"
                            title="Open directly in Jitsi"
                        >
                            <ExternalLink size={17} />
                        </a>
                    )}
                    <button
                        onClick={leave}
                        className="inline-flex items-center gap-2 rounded-xl bg-red-600 px-3 py-2.5 text-sm font-semibold hover:bg-red-500"
                    >
                        <PhoneOff size={16} />
                        <span className="hidden sm:inline">End call</span>
                    </button>
                </div>
            </header>

            <main className="relative min-h-0 flex-1 bg-[#0b0b0b]">
                {error ? (
                    <div className="absolute inset-0 grid place-items-center p-8 text-center">
                        <div>
                            <Video className="mx-auto mb-4 text-red-400" size={40} />
                            <h2 className="text-xl font-semibold">Meeting could not be opened</h2>
                            <p className="mt-2 text-sm text-white/50">{error}</p>
                        </div>
                    </div>
                ) : (
                    <>
                        {!call && (
                            <div className="absolute inset-0 z-10 grid place-items-center bg-[#111]">
                                <Loading />
                            </div>
                        )}
                        <div ref={host} className="absolute inset-0 [&>iframe]:block" />
                    </>
                )}
            </main>
        </div>
    );
}
