import React, { useState } from 'react';
import Select, { components, OptionProps, GroupBase, DropdownIndicatorProps } from 'react-select';

const MonthOption: React.FC<OptionProps<{value: string; label: string; }, false, GroupBase<{value: string; label: string; }>>> = ({ data, innerProps, isSelected }) => {
    const { value, label } = data;
    const { onMouseMove, onMouseOver, ...restInnerProps } = innerProps;

    return (
        <div
            {...restInnerProps}
            className='tw-flex tw-justify-center tw-items-center tw-p-16'
        >
            <span
                className="tw-text-center tw-text-24 tw-font-600 tw-text-white-50"
            >{label}</span>
        </div>
    );
}

const MonthPicker: React.FC = () => {
    const [month, setMonth] = useState<{ value: string; label: string; } | null>(null);

    const handleChange = (selectedOption: {value: string; label: string; } | null) => {
        setMonth(selectedOption);
    }

    const options = [
        { value: '01', label: 'January' },
        { value: '02', label: 'February' },
        { value: '03', label: 'March' },
        { value: '04', label: 'April' },
        { value: '05', label: 'May' },
        { value: '06', label: 'June' },
        { value: '07', label: 'July' },
        { value: '08', label: 'August' },
        { value: '09', label: 'September' },
        { value: '10', label: 'October' },
        { value: '11', label: 'November' },
        { value: '12', label: 'December' }
    ];

    return (
        <Select
            placeholder="Month"
            value={month}
            onChange={handleChange}
            options={options}
            components={{ Option: MonthOption }}
            unstyled
            className="tw-flex tw-justify-center tw-items-center tw-p-16  tw-border tw-border-solid tw-rounded-8 tw-border-white/50 tw-text-white/50 tw-text-center tw-text-24 tw-font-600 tw-text-white-50"
        />
    )
}

export const YearInput: React.FC = () => {
    const [year, setYear] = useState<string>('');

    const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const { value } = event.target;
        const regex = /^[0-9\b]{0,4}$/;

        if (regex.test(value)) {
            setYear(value);
        }
    };

    return (
        <input
            type="text"
            placeholder="Year"
            value={year}
            onChange={handleChange}
            maxLength={4}
            className="tw-flex tw-justify-center tw-bg-transparent tw-items-center tw-p-16  tw-border tw-border-solid tw-rounded-8 tw-border-white/50 tw-text-white/50 tw-text-center tw-text-24 tw-font-600 tw-text-white-50"
        />
    )
}

export const DatePicker: React.FC = () => {
    return (
        <div className="tw-flex tw-gap-16">
            <MonthPicker />
            <YearInput />
        </div>
    )
}
