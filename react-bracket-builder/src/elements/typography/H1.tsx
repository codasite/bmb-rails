export const H1 = (props: {
  children: React.ReactNode
  className?: string
}) => {
  const defaultClasses = 'tw-text-32 sm:tw-text-48 md:tw-text-64 lg:tw-text-80'
  const classes = props.className
    ? `${defaultClasses} ${props.className}`
    : defaultClasses
  return <h1 className={classes}>{props.children}</h1>
}
