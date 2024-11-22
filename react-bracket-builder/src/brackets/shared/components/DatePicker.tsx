// DO NOT REMOVE REACT IMPORT. Needed for image generator
import React, { useRef, useState } from 'react'
import { ReactComponent as CalendarIcon } from '../assets/calendar.svg'
import { BufferedTextInput } from './BufferedTextInput'

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
    e.preventDefault()
    onClick && onClick()
  }
  return (
    <button onMouseDown={handleMonthClick} className={className}>
      {value}
    </button>
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
  onHasError?: (error: string) => void
  onErrorCleared?: () => void
}

const MonthPicker = (props: MonthPickerProps) => {
  const { handleMonthChange, extraClass, value, onErrorCleared, onHasError } =
    props
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
    if (validateMonth(newValue)) {
      handleMonthChange(newValue)
      onErrorCleared?.()
    }
    setEditing(false)
  }

  const onStartEditing = () => {
    setEditing(true)
  }

  const handleMonthClick = async (month: string) => {
    handleDoneEditing(month)
    setTimeout(() => {
      inputRef.current?.blur()
    }, 500)
  }

  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const { value } = event.target
    const filtered = searchArray(months, value)
    setFoundMonths(filtered)
  }

  const validateMonth = (newValue: string) => {
    return !newValue || months.includes(newValue.toUpperCase())
  }

  return (
    <div className="tw-flex tw-flex-col">
      <DatePickerTextInput
        inputRef={inputRef}
        initialValue={value}
        onDoneEditing={handleDoneEditing}
        onStartEditing={onStartEditing}
        onHasError={onHasError}
        onErrorCleared={onErrorCleared}
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
  onHasError?: (error: string) => void
  onErrorCleared?: () => void
}

export const YearInput: React.FC<YearProps> = ({
  handleYearChange,
  extraClass,
  value,
  onHasError,
  onErrorCleared,
}) => {
  const onDoneEditing = (newValue: string) => {
    if (validateYear(newValue)) {
      handleYearChange(newValue)
      onErrorCleared?.()
    }
  }

  const validateYear = (newValue: string) => {
    const regex = /^[0-9\b]{4}$/
    return regex.test(newValue)
  }

  return (
    <DatePickerTextInput
      placeholder="YEAR"
      initialValue={value}
      onDoneEditing={onDoneEditing}
      validate={validateYear}
      onHasError={onHasError}
      onErrorCleared={onErrorCleared}
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
  onHasError?: (error: string) => void
  onErrorCleared?: () => void
  showTitle: boolean
}
export const DatePicker: React.FC<DatePickerProps> = ({
  month,
  year,
  handleMonthChange,
  handleYearChange,
  showTitle,
  onHasError,
  onErrorCleared,
}) => {
  return (
    <div className="tw-flex tw-flex-col tw-justify-center tw-text-center tw-gap-16">
      {showTitle && (
        <span className="tw-text-white/50 tw-text-24 tw-font-500">
          Your Bracket's Date
        </span>
      )}
      <div className="tw-flex tw-flex-col sm:tw-items-start sm:tw-flex-row tw-justify-center tw-gap-16 ">
        <MonthPicker
          value={month}
          handleMonthChange={handleMonthChange}
          extraClass={`tw-flex-grow`}
          onHasError={onHasError}
          onErrorCleared={onErrorCleared}
        />
        <YearInput
          value={year}
          handleYearChange={handleYearChange}
          extraClass={`sm:tw-w-[150px]`}
          onHasError={onHasError}
          onErrorCleared={onErrorCleared}
        />
      </div>
    </div>
  )
}
