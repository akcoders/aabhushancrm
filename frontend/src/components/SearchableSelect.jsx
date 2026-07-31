import { useMemo, useState } from "react";
import { Check, ChevronDown, Search, X } from "lucide-react";

export default function SearchableSelect({
    value,
    onChange,
    options = [],
    placeholder = "Search and select...",
    emptyText = "No matching records",
    required = false,
}) {
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState("");
    const selected = options.find(
        (option) => String(option.value) === String(value),
    );
    const filtered = useMemo(() => {
        const term = search.trim().toLowerCase();
        return term
            ? options.filter((option) =>
                  `${option.label} ${option.search || ""}`
                      .toLowerCase()
                      .includes(term),
              )
            : options;
    }, [options, search]);

    return (
        <div className="relative">
            <button
                type="button"
                onClick={() => setOpen(!open)}
                className="field flex min-h-11 w-full items-center justify-between gap-2 text-left"
            >
                <span
                    className={
                        selected ? "truncate" : "truncate text-[#9a9286]"
                    }
                >
                    {selected?.label || placeholder}
                </span>
                <span className="flex items-center gap-1">
                    {value && !required && (
                        <X
                            size={14}
                            onClick={(event) => {
                                event.stopPropagation();
                                onChange("");
                            }}
                        />
                    )}
                    <ChevronDown size={15} />
                </span>
            </button>
            {open && (
                <div className="absolute z-50 mt-1 w-full overflow-hidden rounded-xl border border-[#ddd6c9] bg-white shadow-xl">
                    <label className="relative block border-b border-[#eee8dc] p-2">
                        <Search
                            className="absolute left-5 top-4 text-[#928a7d]"
                            size={15}
                        />
                        <input
                            autoFocus
                            className="field pl-9"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder={placeholder}
                        />
                    </label>
                    <div className="max-h-56 overflow-y-auto p-1">
                        {filtered.length ? (
                            filtered.map((option) => (
                                <button
                                    type="button"
                                    key={option.value}
                                    onClick={() => {
                                        onChange(option.value);
                                        setSearch("");
                                        setOpen(false);
                                    }}
                                    className="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm hover:bg-[#f6f0e3]"
                                >
                                    <span>{option.label}</span>
                                    {String(option.value) === String(value) && (
                                        <Check
                                            size={15}
                                            className="text-[#aa8131]"
                                        />
                                    )}
                                </button>
                            ))
                        ) : (
                            <p className="p-4 text-center text-xs text-[#8b8376]">
                                {emptyText}
                            </p>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
