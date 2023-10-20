import React, { useState } from 'react'
import Select, {
  components,
  OptionProps,
  GroupBase,
  DropdownIndicatorProps,
  MenuPlacement,
} from 'react-select'
import { ReactComponent as CalendarIcon } from '../assets/calendar.svg'

const MonthOption: React.FC<
  OptionProps<
    { value: string; label: string; backgroundColorClass: string },
    false,
    GroupBase<{ value: string; label: string; backgroundColorClass: string }>
  >
> = ({ data, innerProps, isSelected }) => {
  const { value, label, backgroundColorClass } = data
  const { onMouseMove, onMouseOver, ...restInnerProps } = innerProps

  return (
    <div
      {...restInnerProps}
      className={`tw-flex tw-justify-center tw-items-center tw-p-16 tw-border-b tw-border-b-solid tw-border-b-white/20 ${backgroundColorClass} hover:tw-cursor-pointer hover:tw-bg-greyBlue`}
    >
      <span className="tw-text-center tw-text-24 tw-font-600 tw-text-white-50">
        {label}
      </span>
    </div>
  )
}

interface MonthProps {
  handleMonthChange: (year: string) => void
  backgroundColorClass: string
  menuPlacement: MenuPlacement
}

const MonthPicker: React.FC<MonthProps> = ({
  handleMonthChange,
  backgroundColorClass,
  menuPlacement,
}) => {
  const [month, setMonth] = useState<{ value: string; label: string } | null>(
    null
  )

  const handleChange = (
    selectedOption: { value: string; label: string } | null
  ) => {
    setMonth(selectedOption)
    handleMonthChange(selectedOption?.value || '')
  }

  const options = [
    {
      value: 'January',
      label: 'January',
      backgroundColorClass: backgroundColorClass,
    },
    {
      value: 'February',
      label: 'February',
      backgroundColorClass: backgroundColorClass,
    },
    {
      value: 'March',
      label: 'March',
      backgroundColorClass: backgroundColorClass,
    },
    {
      value: 'April',
      label: 'April',
      backgroundColorClass: backgroundColorClass,
    },
    { value: 'May', label: 'May', backgroundColorClass: backgroundColorClass },
    {
      value: 'June',
      label: 'June',
      backgroundColorClass: backgroundColorClass,
    },
    {
      value: 'July',
      label: 'July',
      backgroundColorClass: backgroundColorClass,
    },
    {
      value: 'August',
      label: 'August',
      backgroundColorClass: backgroundColorClass,
    },
    {
      value: 'September',
      label: 'September',
      backgroundColorClass: backgroundColorClass,
    },
    {
      value: 'October',
      label: 'October',
      backgroundColorClass: backgroundColorClass,
    },
    {
      value: 'November',
      label: 'November',
      backgroundColorClass: backgroundColorClass,
    },
    {
      value: 'December',
      label: 'December',
      backgroundColorClass: backgroundColorClass,
    },
  ]

  const styles = {
    menuList: (base) => ({
      ...base,
      borderRadius: '8px',
      marginBottom: menuPlacement == 'bottom' ? '-3px' : '3px',

      '::-webkit-scrollbar': {
        width: '0px',
        height: '0px',
      },
    }),
  }

  return (
    <Select
      placeholder={
        <div className="tw-flex tw-items-center tw-justify-center tw-gap-16">
          <CalendarIcon /> <span>Month</span>
        </div>
      }
      value={month}
      onChange={handleChange}
      options={options}
      components={{ Option: MonthOption, DropdownIndicator: () => null }}
      unstyled
      styles={styles}
      menuPlacement={menuPlacement}
      className={`tw-flex tw-justify-center tw-items-center tw-p-16 ${backgroundColorClass} tw-border tw-border-solid tw-rounded-8 tw-border-white/50 tw-text-white/50 tw-text-center tw-text-24 tw-font-600 tw-text-white-50 tw-min-w-[344px] tw-h-[62px] focus:tw-border-white`}
    />
  )
}

interface YearProps {
  handleYearChange: (year: string) => void
  backgroundColorClass: string
}

export const YearInput: React.FC<YearProps> = ({
  handleYearChange,
  backgroundColorClass,
}) => {
  const [year, setYear] = useState<string>('')

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = event.target
    const regex = /^[0-9\b]{0,4}$/

    if (regex.test(value)) {
      setYear(value)
      handleYearChange(value)
    }
  }

  return (
    <input
      type="text"
      placeholder="YEAR"
      value={year}
      onChange={handleChange}
      maxLength={4}
      className={`tw-flex tw-justify-center ${backgroundColorClass} tw-items-center tw-p-16  tw-border tw-border-solid tw-rounded-8 tw-border-white/50 tw-text-white/50 tw-text-center tw-text-24 tw-font-600 tw-text-white-50 tw-w-[150px] tw-h-[62px] tw-placeholder-white/50`}
    />
  )
}

interface DatePickerProps {
  handleMonthChange: (year: string) => void
  handleYearChange: (year: string) => void
  showTitle: boolean
  backgroundColorClass: string
  selectMenuPlacement: MenuPlacement
}
export const DatePicker: React.FC<DatePickerProps> = ({
  handleMonthChange,
  handleYearChange,
  showTitle,
  backgroundColorClass,
  selectMenuPlacement,
}) => {
  return (
    <div className="tw-flex tw-flex-col tw-justify-center tw-text-center tw-gap-16">
      {showTitle && (
        <span className="tw-text-white/50 tw-text-24 tw-font-500">
          Your Bracket's Date
        </span>
      )}
      <div className="tw-flex tw-justify-center tw-items-start tw-gap-16 tw-min-w-150 tw-h-[62px]">
        <MonthPicker
          handleMonthChange={handleMonthChange}
          backgroundColorClass={backgroundColorClass}
          menuPlacement={selectMenuPlacement}
        />
        <YearInput
          handleYearChange={handleYearChange}
          backgroundColorClass={backgroundColorClass}
        />
      </div>
    </div>
  )
}
