import React, { useState } from 'react';
import Select, { components, OptionProps, GroupBase, DropdownIndicatorProps } from 'react-select';
import { ReactComponent as CalendarIcon } from '../assets/calendar.svg';

const MonthOption: React.FC<OptionProps<{value: string; label: string; backgroundColorClass: string}, false, GroupBase<{value: string; label: string;  backgroundColorClass: string}>>> = ({ data, innerProps, isSelected }) => {
    const { value, label, backgroundColorClass } = data;
    const { onMouseMove, onMouseOver, ...restInnerProps } = innerProps;

    return (
        <div
            {...restInnerProps}
            className={`tw-flex tw-justify-center tw-items-center tw-p-16 tw-border-b tw-border-b-solid tw-border-b-white/20 ${backgroundColorClass}`}
        >
            <span
                className="tw-text-center tw-text-24 tw-font-600 tw-text-white-50"
            >{label}</span>
        </div>
    );
}

interface MonthProps {
    handleMonthChange: (year: string) => void
    backgroundColorClass: string,
}

const MonthPicker: React.FC<MonthProps> = ({ handleMonthChange, backgroundColorClass }) => {
    const [month, setMonth] = useState<{ value: string; label: string; } | null>(null);

    const handleChange = (selectedOption: {value: string; label: string; } | null) => {
        setMonth(selectedOption);
        handleMonthChange(selectedOption?.value || '');
    }

    const options = [
        { value: '01', label: 'January', backgroundColorClass: backgroundColorClass },
        { value: '02', label: 'February', backgroundColorClass: backgroundColorClass  },
        { value: '03', label: 'March', backgroundColorClass: backgroundColorClass  },
        { value: '04', label: 'April', backgroundColorClass: backgroundColorClass  },
        { value: '05', label: 'May', backgroundColorClass: backgroundColorClass  },
        { value: '06', label: 'June', backgroundColorClass: backgroundColorClass  },
        { value: '07', label: 'July', backgroundColorClass: backgroundColorClass  },
        { value: '08', label: 'August', backgroundColorClass: backgroundColorClass  },
        { value: '09', label: 'September', backgroundColorClass: backgroundColorClass  },
        { value: '10', label: 'October', backgroundColorClass: backgroundColorClass  },
        { value: '11', label: 'November', backgroundColorClass: backgroundColorClass  },
        { value: '12', label: 'December', backgroundColorClass: backgroundColorClass  }
    ];

    const styles = {
        control: (base, state) => ({
            ...base,
            zIndex: "1",
        }),
        menu: (base) => ({
            ...base,
            marginTop: '1px',
            zIndex: "9999",
        }),
        menuList: (base) => ({
          ...base,
          zIndex: 9999,
      
          "::-webkit-scrollbar": {
            width: "1px",
            height: "0px",
          },
          "::-webkit-scrollbar-track": {
            background: "#f1f1f1"
          },
          "::-webkit-scrollbar-thumb": {
            background: "#888"
          },
          "::-webkit-scrollbar-thumb:hover": {
            background: "#555"
          }
        }),
        option: (base, state) => ({
            ...base,
            cursor: 'pointer',
        }),
      }

    return (
        <Select
            placeholder={<><CalendarIcon /> Month</>}
            value={month}
            onChange={handleChange}
            options={options}
            components={{ Option: MonthOption, DropdownIndicator: () => null }}
            unstyled
            styles={styles}
            menuPlacement="bottom"
            className={`tw-flex tw-justify-center tw-items-center tw-p-16 ${backgroundColorClass} tw-border tw-border-solid tw-rounded-8 tw-border-white/50 tw-text-white/50 tw-text-center tw-text-24 tw-font-600 tw-text-white-50 tw-min-w-344 tw-h-62 focus:tw-border-white`}

            
        />
    )
}

interface YearProps {
    handleYearChange: (year: string) => void;
    backgroundColorClass: string,
}

export const YearInput: React.FC<YearProps> = ({handleYearChange, backgroundColorClass}) => {
    const [year, setYear] = useState<string>('');

    const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const { value } = event.target;
        const regex = /^[0-9\b]{0,4}$/;

        if (regex.test(value)) {
            setYear(value);
            handleYearChange(value);
        }
    };

    return (
        <input
            type="text"
            placeholder="YEAR"
            value={year}
            onChange={handleChange}
            maxLength={4}
            className={`tw-flex tw-justify-center ${backgroundColorClass} tw-items-center tw-p-16  tw-border tw-border-solid tw-rounded-8 tw-border-white/50 tw-text-white/50 tw-text-center tw-text-24 tw-font-600 tw-text-white-50 tw-w-150 tw-h-62`}
        />
    )
}

interface DatePickerProps {
    handleMonthChange: (year: string) => void;
    handleYearChange: (year: string) => void;
    showTitle: boolean,
    backgroundColorClass: string,
}
export const DatePicker: React.FC<DatePickerProps> = ({handleMonthChange, handleYearChange, showTitle, backgroundColorClass}) => {
    return (
        <div className="tw-flex tw-flex-col tw-justify-center tw-text-center tw-gap-16">
            { showTitle && (
                <span
                    className="tw-text-white/50 tw-text-24 tw-font-500"
                >
                    Your Bracket's Date
                </span>
            )}
            <div className="tw-flex tw-justify-center tw-items-start tw-gap-16 tw-min-w-150 tw-h-62">
                <MonthPicker
                    handleMonthChange={handleMonthChange}
                    backgroundColorClass={backgroundColorClass}

                 />
                <YearInput
                    handleYearChange={handleYearChange}
                    backgroundColorClass={backgroundColorClass}
                 />
            </div>
        </div>
    )
}
