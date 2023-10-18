import React, { useState } from 'react';
import Select, { components, OptionProps, GroupBase } from 'react-select';

const MonthOption: React.FC<OptionProps<{value: string; label: string; }, false, GroupBase<{value: string; label: string; }>>> = ({ data, innerProps, isSelected }) => {
    const { value, label } = data;
    const { onMouseMove, onMouseOver, ...restInnerProps } = innerProps;

    return (
        <div
            {...restInnerProps}
            className='tw-flex tw-justify-center tw-items-center tw-p-16 tw-bg-transparent'
        >
            <span>{label}</span>
        </div>
    );
}

export const MonthPicker: React.FC = () => {
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
            value={month}
            onChange={handleChange}
            options={options}
            components={{ Option: MonthOption }}
            className="tw-flex tw-justify-center tw-items-center tw-p-16 tw-bg-transparent"
        />
    )
}