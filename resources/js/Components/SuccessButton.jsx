export default function SuccessButton({ className = '', disabled, children, onclick, value, ...props }) {

    return (
        <button
            {...props}
            className={
                `inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 ${
                    disabled && 'opacity-25'
                } ` + className
            }
            disabled={disabled}
            value={value}
            onClick={onclick}
            
        >
            {children}
        </button>
    );
}
