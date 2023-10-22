import React, { useState, useEffect } from 'react'
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

interface PlaceholderWrapperProps {
  children: React.ReactNode
  extraClass?: string
}

const PlaceholderWrapper = (props: PlaceholderWrapperProps) => {
  const { children, extraClass } = props
  return (
    <div className="tw-absolute tw-top-1/2 tw-left-1/2 tw--translate-x-1/2 tw--translate-y-1/2 tw-pointer-events-none">
      {children}
    </div>
  )
}

interface BufferedTextInputProps {
  initialValue?: string
  placeholderEl?: React.ReactNode
  onDoneEditing?: (newValue) => void
  onStartEditing?: () => void
  [key: string]: any
}

const BufferedTextInput = (props: BufferedTextInputProps) => {
  const {
    initialValue,
    onChange,
    extraClass,
    onStartEditing,
    onDoneEditing,
    placeholderEl,
  } = props
  const [showPlaceholder, setShowPlacholder] = useState<boolean>(true)
  const [buffer, setBuffer] = useState<string>('')

  useEffect(() => {
    setBuffer(initialValue)
  }, [initialValue])

  const doneEditing = () => {
    if (!buffer) {
      setShowPlacholder(true)
    }
    if (onDoneEditing) {
      onDoneEditing(buffer)
    }
  }

  const startEditing = () => {
    setShowPlacholder(false)
    if (onStartEditing) {
      onStartEditing()
    }
  }

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = event.target
    console.log(value)
    setBuffer(value)
    if (onChange) {
      onChange(event)
    }
  }

  return (
    <div className="tw-relative">
      {showPlaceholder && placeholderEl && (
        <PlaceholderWrapper>{placeholderEl}</PlaceholderWrapper>
      )}
      <input
        type="text"
        onFocus={(e) => {
          startEditing()
          e.target.select()
        }}
        onBlur={() => doneEditing()}
        onKeyUp={(e) => {
          if (e.key === 'Enter') {
            doneEditing()
            e.currentTarget.blur()
          }
        }}
        value={buffer}
        onChange={handleChange}
        {...props}
      />
    </div>
  )
}

interface DatePickerTextInputProps {
  extraClass?: string
  initialValue?: string
  [key: string]: any
}

const DatePickerTextInput = (props: DatePickerTextInputProps) => {
  const { initialValue, extraClass } = props
  const bgClass = initialValue ? 'tw-bg-white/5' : 'tw-bg-transparent'
  console.log(initialValue)
  const classes = `tw-font-sans tw-uppercase tw-p-16 tw-border tw-border-solid tw-rounded-8 tw-border-white/50 tw-text-white/50 tw-text-center tw-text-24 tw-font-600 tw-text-white-50 tw-placeholder-white/50 ${bgClass} focus:tw-placeholder-transparent focus:tw-outline-none focus:tw-bg-white/5`
  const className = [classes, extraClass].join(' ')

  return <BufferedTextInput className={className} {...props} />
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
  value?: string
  extraClass?: string
}

const MonthPicker = (props: MonthPickerProps) => {
  const { handleMonthChange, extraClass, value } = props
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

  const handleDoneEditing = (newValue) => {
    handleMonthChange(newValue)
  }

  return (
    <DatePickerTextInput
      initialValue={value}
      onDoneEditing={handleDoneEditing}
      onStartEditing={() => setEditing(true)}
      extraClass={extraClass}
      placeholderEl={
        <div className="tw-flex tw-items-center tw-justify-center tw-gap-16 tw-pointer-events-none">
          <CalendarIcon />
          <span className="tw-text-24 tw-font-600 tw-text-white/50">Month</span>
        </div>
      }
    />
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

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = event.target
    const regex = /^[0-9\b]{0,4}$/

    if (regex.test(value)) {
      setYear(value)
      handleYearChange(value)
    }
  }

  return (
    <DatePickerTextInput
      type="text"
      placeholder="YEAR"
      value={year}
      onChange={handleChange}
      maxLength={4}
      extraClass={extraClass}
    />
  )
}

interface DatePickerProps {
  month?: string
  year?: string
  handleMonthChange: (year: string) => void
  handleYearChange: (year: string) => void
  showTitle: boolean
  backgroundColorClass: string
  selectMenuPlacement: MenuPlacement
}
export const DatePicker: React.FC<DatePickerProps> = ({
  month,
  year,
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
          value={month}
          handleMonthChange={handleMonthChange}
          extraClass={`tw-flex-grow`}
        />
        <YearInput
          handleYearChange={handleYearChange}
          extraClass={`sm:tw-w-[150px]`}
        />
      </div>
    </div>
  )
}
