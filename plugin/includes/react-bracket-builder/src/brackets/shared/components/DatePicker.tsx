import React, { useState, useEffect, useRef } from 'react'
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
  onDoneEditing?: (newValue: string) => void
  onStartEditing?: () => void
  validate?: (newValue: string) => boolean
  className?: string
  errorText?: string
  [key: string]: any
}

const BufferedTextInput = (props: BufferedTextInputProps) => {
  const {
    inputRef,
    initialValue,
    onChange,
    onStartEditing,
    onDoneEditing,
    placeholderEl,
    validate,
    errorText,
  } = props
  const [showPlaceholder, setShowPlacholder] = useState<boolean>(true)
  const [buffer, setBuffer] = useState<string>('')
  const [hasError, setHasError] = useState<boolean>(false)
  const errorClass = 'tw-border-red tw-text-red'
  const extraClass = hasError ? errorClass : ''
  const className = [props.className, extraClass].join(' ')

  useEffect(() => {
    setBuffer(initialValue ?? '')
  }, [initialValue])

  const doneEditing = () => {
    if (!buffer) {
      setShowPlacholder(true)
    }
    if (onDoneEditing && !hasError) {
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

    if (validate) {
      setHasError(!validate(value))
    }
    setBuffer(value)
    if (onChange) {
      onChange(event)
    }
  }

  return (
    <div className="tw-relative tw-flex tw-flex-col tw-gap-8">
      {showPlaceholder && placeholderEl && (
        <PlaceholderWrapper>{placeholderEl}</PlaceholderWrapper>
      )}
      <input
        ref={props.inputRef}
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
        {...props}
        onChange={handleChange}
        className={className}
      />
      {hasError && errorText && (
        <span className="tw-text-red tw-text-12 tw-font-sans tw-text-left ">
          {errorText}
        </span>
      )}
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
  const classes = `tw-font-sans tw-uppercase tw-p-16 tw-border tw-border-solid tw-rounded-8 tw-border-white/50 tw-text-white/50 tw-text-center tw-text-24 tw-font-600 tw-text-white-50 tw-placeholder-white/50 ${bgClass} focus:tw-placeholder-transparent focus:tw-outline-none focus:tw-bg-white/5`
  const className = [classes, extraClass].join(' ')

  return <BufferedTextInput className={className} {...props} />
}

interface MonthOptProps {
  value?: string
  extraClass?: string
  onClick?: () => void
}

const MonthOpt = (props: MonthOptProps) => {
  const { value, extraClass, onClick } = props
  const classes = `tw-p-16 tw-font-sans tw-uppercase tw-w-full tw-border-none tw-border-b tw-border-b-solid hover:tw-cursor-pointer tw-border-b-white/5 tw-bg-transparent hover:tw-bg-white/15 tw-text-center tw-text-24 tw-font-600 tw-text-white/50`
  const className = [classes, extraClass].join(' ')
  const handleMonthClick = (e) => {
    console.log('handle month click')
    console.log(value)
    e.preventDefault()
    onClick && onClick()
  }
  return (
    <button onMouseDown={handleMonthClick} className={className}>
      {value}
    </button>
  )
}

interface MonthOptionsProps {}
const MonthOptions = (props: MonthOptionsProps) => {
  return (
    <div className="tw-flex tw-flex-col tw-items-center tw-rounded-8 tw-bg-white/5"></div>
  )
}

const searchArray = (arr: string[], query: string) => {
  if (!query) {
    return []
  }
  const regex = new RegExp(`${query.trim()}`, 'i')
  return arr.filter((item) => item.search(regex) >= 0)
}

interface MonthPickerProps {
  handleMonthChange: (month: string) => void
  value?: string
  extraClass?: string
}

const MonthPicker = (props: MonthPickerProps) => {
  const { handleMonthChange, extraClass, value } = props
  const [editing, setEditing] = useState<boolean>(false)
  const [foundMonths, setFoundMonths] = useState<string[]>([])
  const inputRef = useRef<HTMLInputElement>(null)
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

  const handleDoneEditing = (newValue: string) => {
    handleMonthChange(newValue)
    setEditing(false)
  }

  const onStartEditing = () => {
    console.log('start editing')
    setEditing(true)
  }

  const handleMonthClick = async (month: string) => {
    handleDoneEditing(month)
    console.log('handle month click')
    console.log(inputRef.current)
    setTimeout(() => {
      inputRef.current?.blur()
    }, 100)
  }

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = event.target
    console.log('handle change')
    console.log(value)
    const filtered = searchArray(months, value)
    console.log(filtered)
    setFoundMonths(filtered)
  }

  const validateMonth = (newValue: string) => {
    return !newValue || searchArray(months, newValue).length > 0
  }

  return (
    <div className="tw-flex tw-flex-col">
      <DatePickerTextInput
        inputRef={inputRef}
        initialValue={value}
        onDoneEditing={handleDoneEditing}
        onStartEditing={onStartEditing}
        extraClass={extraClass}
        onChange={handleChange}
        validate={validateMonth}
        errorText="Invalid month"
        placeholderEl={
          <div className="tw-flex tw-items-center tw-justify-center tw-gap-16 tw-pointer-events-none">
            <CalendarIcon />
            <span className="tw-text-24 tw-font-600 tw-text-white/50">
              Month
            </span>
          </div>
        }
      />
      {editing && (
        <ul className="tw-list-none tw-m-0 tw-p-0 tw-flex tw-flex-col tw-rounded-b-8 tw-bg-white/5 tw-overflow-hidden">
          {foundMonths.map((month, i) => (
            <li>
              <MonthOpt value={month} onClick={() => handleMonthClick(month)} />
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}

interface YearProps {
  handleYearChange: (year: string) => void
  value?: string
  extraClass?: string
}

export const YearInput: React.FC<YearProps> = ({
  handleYearChange,
  extraClass,
  value,
}) => {
  const onDoneEditing = (newValue: string) => {
    handleYearChange(newValue)
  }

  const validateYear = (newValue: string) => {
    const regex = /^[0-9\b]{0,4}$/
    return regex.test(newValue)
  }

  return (
    <DatePickerTextInput
      placeholder="YEAR"
      initialValue={value}
      onDoneEditing={onDoneEditing}
      validate={validateYear}
      errorText="Invalid year"
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
      <div className="tw-flex tw-flex-col tw-items-start sm:tw-flex-row tw-justify-center tw-gap-16 ">
        <MonthPicker
          value={month}
          handleMonthChange={handleMonthChange}
          extraClass={`tw-flex-grow`}
        />
        <YearInput
          value={year}
          handleYearChange={handleYearChange}
          extraClass={`sm:tw-w-[150px]`}
        />
      </div>
    </div>
  )
}
