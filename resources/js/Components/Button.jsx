export default function Button({ className = '', disabled, children, ...props }) {
    return (
        <button
            {...props}
            className={
                `inline-flex items-center px-4 py-2 bg-${props.color}-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-${props.color}-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-${props.color}-500 focus:ring-offset-2 transition ease-in-out duration-150 ${
                    disabled && 'opacity-25'
                } ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
