import checkIcon from '../../shared/assets/check.svg'

export interface CustomCheckboxProps {
  id: string
  checked: boolean
  onChange: (e: any) => void
  height?: number
  width?: number
}

export const CustomCheckbox = (props: any) => {
  const { id, checked, onChange, height = 32, width = 32 } = props

  const baseStyles = ['tw-appearance-none', 'tw-rounded-8', 'tw-cursor-pointer']

  const uncheckedStyles = ['tw-border', 'tw-border-solid', 'tw-border-white']

  const checkedStyles = ['tw-bg-white', 'tw-bg-no-repeat', 'tw-bg-center']

  const styles = baseStyles
    .concat(checked ? checkedStyles : uncheckedStyles)
    .join(' ')

  return (
    <input
      type="checkbox"
      id={id}
      className={styles}
      checked={checked}
      onChange={onChange}
      style={{
        backgroundImage: checked ? `url(${checkIcon})` : 'none',
        height,
        width,
      }}
    />
  )
}
