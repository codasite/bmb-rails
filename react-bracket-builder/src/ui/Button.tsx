const Button = (props: {
  onClick?: () => void
  children?: React.ReactNode
  disabled?: boolean
  // default white
  color?: 'green' | 'white'
  // default outlined
  variant?: 'outlined' | 'filled'
  // default medium
  size?: 'small' | 'medium' | 'large'
  className?: string
  text?: string
  icon?: React.ReactNode
}) => {
  const defaultClasses = [
    'disabled:tw-cursor-default',
    'disabled:tw-text-white/50',
    'tw-cursor-pointer',
    'tw-flex',
    'tw-flex-row',
    'tw-font-sans',
    'tw-gap-16',
    'tw-items-center',
    'tw-justify-center',
    'tw-leading-none',
    'tw-rounded-8',
    'tw-text-16',
    'tw-text-white',
    'tw-uppercase',
    'tw-w-full',
    'tw-whitespace-nowrap',
  ]
  switch (props.variant) {
    case 'filled':
      defaultClasses.push('tw-border-none', 'tw-font-500')
      break
    case 'outlined':
    default:
      defaultClasses.push(
        'tw-border',
        'tw-border-1',
        'tw-border-solid',
        'tw-font-700'
      )
      break
  }
  switch (props.color) {
    case 'green':
      defaultClasses.push(
        'tw-bg-green/15',
        'enabled:hover:tw-bg-green/20',
        'tw-border-green',
        'disabled:tw-border-green/50'
      )
      break
    case 'white':
    default:
      defaultClasses.push(
        'tw-bg-white/15',
        'enabled:hover:tw-bg-white/20',
        'tw-border-white',
        'disabled:tw-border-white/50'
      )
      break
  }
  switch (props.size) {
    case 'small':
      defaultClasses.push('tw-p-8')
      break
    case 'large':
      defaultClasses.push('tw-p-16')
      break
    case 'medium':
    default:
      defaultClasses.push('tw-p-12')
      break
  }
  const classes = [
    ...defaultClasses,
    ...(props.className ? props.className.split(' ') : []),
  ].join(' ')

  return (
    <button
      className={classes}
      disabled={props.disabled}
      onClick={props.onClick}
    >
      {props.text && <span>{props.text}</span>}
      {props.children}
    </button>
  )
}

export default Button
