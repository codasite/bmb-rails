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
  menuPlacement: MenuPlacement
  backgroundColorClass: string
  extraClass?: string
}

const MonthPickerOld: React.FC<MonthProps> = ({
  handleMonthChange,
  menuPlacement,
  backgroundColorClass,
  extraClass,
}) => {
  const [month, setMonth] = useState<{ value: string; label: string } | null>(
    null
  )
  const classes = `${backgroundColorClass} tw-flex tw-justify-center tw-items-center tw-p-16 tw-border tw-border-solid tw-rounded-8 tw-border-white/50 tw-text-white/50 tw-text-center tw-text-24 tw-font-600 tw-text-white-50 focus:tw-border-white`
  const className = [classes, extraClass].join(' ')

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
      className={className}
    />
  )
}

interface DatePickerTextInputProps {
  extraClass?: string
  [key: string]: any
}

const DatePickerTextInput = (props: DatePickerTextInputProps) => {
  const { value, onChange, placeholder, extraClass } = props
  const bgClass = value ? 'tw-bg-white/5' : 'tw-bg-transparent'
  const classes = `tw-font-sans tw-uppercase tw-p-16 tw-border tw-border-solid tw-rounded-8 tw-border-white/50 tw-text-white/50 tw-text-center tw-text-24 tw-font-600 tw-text-white-50 tw-placeholder-white/50 ${bgClass} focus:tw-placeholder-transparent focus:tw-outline-none focus:tw-bg-white/5`
  const className = [classes, extraClass].join(' ')

  return (
    <input
      onFocus={(e) => e.target.select()}
      type="text"
      placeholder={placeholder}
      value={value}
      onChange={onChange}
      className={className}
      {...props}
    />
  )
}

interface MonthPickerButtonProps {
  children: React.ReactNode
  onClick: (e: React.MouseEvent<HTMLButtonElement>) => void
  extraClass?: string
}
const MonthPickerButton = (props: MonthPickerButtonProps) => {
  const { children, onClick, extraClass } = props
  const classes = `tw-font-sans tw-uppercase tw-p-16 tw-border tw-border-solid tw-rounded-8 tw-border-white/50 tw-text-white/50 tw-text-center tw-text-24 tw-font-600 tw-text-white-50 tw-placeholder-white/50 focus:tw-placeholder-transparent focus:tw-outline-none focus:tw-bg-white/5`
  const btnClasses = `tw-flex-grow tw-bg-transparent hover:tw-cursor-pointer`
  const className = [classes, btnClasses, extraClass].join(' ')
  return (
    <button type="button" className={className} onClick={onClick}>
      {/* <button type="button" className={className}> */}
      {children}
    </button>
  )
}

interface MonthPickerProps {
  handleMonthChange: (month: string) => void
  extraClass?: string
}

const MonthPicker = (props: MonthPickerProps) => {
  const { handleMonthChange, extraClass } = props
  const [monthText, setMonthText] = useState<string>('')
  const [editing, setEditing] = useState<boolean>(false)
  const [month, setMonth] = useState<number | null>(null)
  const months = [
    'JANUARY',
    'FEBRUARY',
    'MARCH',
    'APRIL',
    'MAY',
    'JUNE',
    'JULY',
    'AUGUST ',
    'SEPTEMBER',
    'OCTOBER',
    'NOVEMBER',
    'DECEMBER',
  ]

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = event.target
    setMonthText(value)
    handleMonthChange(value)
    // setMonthText(value)
    // const monthIndex = months.indexOf(value)
    // if (monthIndex >= 0) {
    //   setMonth(monthIndex + 1)
    //   handleMonthChange(`${monthIndex + 1}`)
    // } else {
    //   setMonth(null)
    //   handleMonthChange('')
    // }
  }

  const monthPlaceholder = (
    <div className="tw-absolute tw-top-1/2 tw-left-1/2 tw--translate-x-1/2 tw--translate-y-1/2 tw-absolute tw-flex tw-items-center tw-justify-center tw-gap-16 tw-pointer-events-none">
      <CalendarIcon />{' '}
      <span className="tw-text-24 tw-font-600 tw-text-white/50">Month</span>
    </div>
  )
  const showPlaceholder = !editing && !monthText

  return (
    <div className="tw-relative">
      {showPlaceholder && monthPlaceholder}
      <DatePickerTextInput
        value={monthText}
        onChange={handleChange}
        extraClass={extraClass}
        onBlur={() => setEditing(false)}
        onKeyUp={(e) => {
          if (e.key === 'Enter') {
            setEditing(false)
          }
        }}
        onFocus={(e) => {
          setEditing(true)
          e.target.select()
        }}
      />
    </div>
  )
}

interface YearProps {
  handleYearChange: (year: string) => void
  extraClass?: string
}

export const YearInput: React.FC<YearProps> = ({
  handleYearChange,
  extraClass,
}) => {
  const [year, setYear] = useState<string>('')
  const classes = `tw-p-16 tw-border tw-border-solid tw-rounded-8 tw-border-white/50 tw-text-white/50 tw-text-center tw-text-24 tw-font-600 tw-placeholder-white/50 !tw-bg-transparent focus:tw-placeholder-transparent focus:tw-outline-none`
  const className = [classes, extraClass].join(' ')

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
      className={className}
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
      <div className="tw-flex tw-flex-col sm:tw-flex-row tw-justify-center tw-gap-16 ">
        <MonthPicker
          handleMonthChange={handleMonthChange}
          extraClass={`tw-flex-grow`}
        />
        <YearInput
          handleYearChange={handleYearChange}
          extraClass={`${backgroundColorClass} sm:tw-w-[150px]`}
        />
      </div>
    </div>
  )
}
