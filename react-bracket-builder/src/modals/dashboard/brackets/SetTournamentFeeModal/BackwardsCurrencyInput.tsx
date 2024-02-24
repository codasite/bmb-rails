import { useEffect, useState } from 'react'

const getCurrencyFormatter = (allowCents: boolean) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: allowCents ? 2 : 0,
    maximumFractionDigits: allowCents ? 2 : 0,
  })
}

export function BackwardsCurrencyInput(props: {
  value?: number // whole dollar amount if allowCents is false, otherwise cents
  onChange?: (value: number) => void
  allowCents?: boolean
  classNames?: string
}) {
  const [strValue, setStrValue] = useState(
    props.value ? props.value.toString() : '0'
  )

  useEffect(() => {
    props.onChange && props.onChange(parseInt(strValue))
  }, [strValue])

  const keyPressHandler = (event) => {
    const { key } = event
    console.log(strValue)
    console.log(typeof strValue)
    setStrValue((prevValue) => {
      // if you press backspace and it's a length of 1, return 0
      if (prevValue.length === 1 && key === 'Backspace') {
        return '0'
      }
      if (key === 'Backspace') {
        return prevValue.substring(0, prevValue.length - 1)
      }

      if (!Number.isNaN(parseInt(key))) {
        if (prevValue === '0') {
          return key
        }
        return prevValue + key
      }
      return prevValue
    })
  }

  let formattedValue = getCurrencyFormatter(props.allowCents).format(
    parseInt(strValue) / (props.allowCents ? 100 : 1)
  )

  return (
    <input
      name="currency-input"
      onKeyDown={keyPressHandler}
      onBlur={() => {
        if (strValue.endsWith('.')) {
          setStrValue((prevValue) =>
            prevValue.substring(0, prevValue.length - 1)
          )
        }
      }}
      placeholder={getCurrencyFormatter(false).format(0)}
      value={formattedValue}
      onChange={() => {}}
      className={props.classNames}
    />
  )
}
